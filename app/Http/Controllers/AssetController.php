<?php

namespace App\Http\Controllers;

use App\Cache\CacheKeys;
use App\Models\AssetTypeImage;
use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

class AssetController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index(Request $request)
    {
        try {
            $client = $this->neo4j->client();

            $categories = Cache::remember(CacheKeys::ASSETS_CATEGORIES, CacheKeys::TTL_LONG, function () use ($client) {
                $cats = [];
                foreach ($client->run('MATCH (a:Asset) WHERE a.type IS NOT NULL RETURN a.type as type, count(a) as count ORDER BY count DESC') as $record) {
                    $cats[] = ['name' => $record->get('type'), 'count' => $record->get('count')];
                }
                return $cats;
            });

            // Merge type images (MySQL) into categories
            $typeImages = Cache::remember(CacheKeys::ASSET_TYPE_IMAGES, CacheKeys::TTL_LONG, function () {
                return AssetTypeImage::all()->keyBy('type')->map->image_url->toArray();
            });
            foreach ($categories as &$cat) {
                $cat['imageUrl'] = $typeImages[strtoupper($cat['name'])] ?? null;
            }
            unset($cat);

            // Fallback default categories if empty
            if (empty($categories)) {
                $defaultTypes = ['IMG', 'GIF', 'VIDEO', 'AMV', 'MUSIC', 'ANIME', 'MANGA', 'LIGHT NOVEL', 'DOUJIN', 'WALLPAPER ENGINE'];
                foreach ($defaultTypes as $dt) {
                    $categories[] = ['name' => $dt, 'count' => 0];
                }
            }

            // Fetch the latest assets
            $assets = [];
            $query = 'MATCH (a:Asset) ';
            $params = [];

            $typeFilter = $request->query('type');
            if ($typeFilter) {
                $query .= 'WHERE a.type = $type ';
                $params['type'] = $typeFilter;
            }

            $query .= '
                OPTIONAL MATCH (c:Character)-[:HAS_ASSET]->(a)
                RETURN a, count(c) as charactersCount
                ORDER BY a.createdAt DESC
                LIMIT 50
            ';
            $resAssets = $client->run($query, $params);
            foreach ($resAssets as $rec) {
                $a = $rec->get('a')->getProperties()->toArray();
                
                // Construct standard paths
                $fileUrl = $a['url'] ?? null;
                if (!$fileUrl && isset($a['filename'])) {
                    $fileUrl = asset('storage/assets/' . $a['filename']);
                }

                $coverUrl = null;
                if (isset($a['coverFilename'])) {
                    $coverUrl = asset('storage/assets/covers/' . $a['coverFilename']);
                }

                $a['fileUrl'] = $fileUrl;
                $a['coverUrl'] = $coverUrl;
                $a['tagsCount'] = $rec->get('charactersCount');

                // Fix Neo4j DateTime formatting
                $createdAt = $a['createdAt'] ?? now();
                if (is_object($createdAt)) {
                    if (method_exists($createdAt, 'toDateTime')) {
                        $createdAt = $createdAt->toDateTime();
                    } elseif (method_exists($createdAt, 'format')) {
                        $createdAt = $createdAt->format('Y-m-d H:i:s');
                    } else {
                        // Final fallback just in case
                        try {
                            $createdAt = (string) $createdAt;
                        } catch (\Exception $e) {
                            $createdAt = now()->format('Y-m-d H:i:s');
                        }
                    }
                }
                $a['createdAt'] = $createdAt;

                $assets[] = $a;
            }

            return view('welcome', compact('categories', 'assets'));

        } catch (Exception $e) {
            return view('welcome', ['categories' => [], 'assets' => [], 'error' => $e->getMessage()]);
        }
    }

    public function create()
    {
        try {
            $client = $this->neo4j->client();

            $franchises = app(\App\Actions\GetFranchiseNamesAction::class)->execute();

            return view('assets.create', compact('franchises'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request, \App\Actions\CreateAssetAction $createAssetAction)
    {
        $request->validate([
            'files' => 'nullable|array',
            'files.*' => 'file|max:524288',
            'url' => 'nullable|url|max:500',
            'title' => 'nullable|string|max:255',
            'asset_type' => 'required|string|in:ANIME,MANGA,LIGHT NOVEL,DOUJIN,WALLPAPER ENGINE,IMG,MUSIC,GIF,AMV',
            'cover_image' => 'nullable|image|max:10240',
            'cover_url' => 'nullable|url|max:1000',
            'characters' => 'nullable|array',
            'characters.*' => 'integer',
            'media' => 'nullable|array',
            'media.*' => 'integer',
        ]);

        try {
            $characterIds = array_values(array_unique(array_map('intval', $request->input('characters', []))));
            $mediaIds = array_values(array_unique(array_map('intval', $request->input('media', []))));

            if (empty($characterIds) && empty($mediaIds)) {
                return back()->with('error', 'Debes vincular el recurso al menos a un personaje o a una obra (media).');
            }

            $files = $request->file('files');
            if (!$files && !$request->input('url')) {
                return back()->with('error', 'Debes proporcionar al menos un archivo o una URL válida.');
            }

            $totalLinkedCount = 0;
            $itemsCreated = 0;
            $baseTitle = $request->input('title');

            if ($files && is_array($files)) {
                // Determine if we need padding (e.g. '01', '02' instead of '1', '2')
                $padLength = strlen((string)count($files));
                
                foreach ($files as $index => $file) {
                    // Si se provee un título base y hay múltiples archivos, los enumeramos. 
                    // Ej: "Bleach Episode" -> "Bleach Episode 01", "Bleach Episode 02"
                    $itemTitle = null;
                    if ($baseTitle) {
                        $itemTitle = count($files) > 1 
                            ? $baseTitle . ' ' . str_pad($index + 1, max(2, $padLength), '0', STR_PAD_LEFT) 
                            : $baseTitle;
                    }
                    
                    $count = $createAssetAction->execute(
                        $file,
                        null,
                        $itemTitle,
                        $request->input('asset_type'),
                        $request->file('cover_image'),
                        $characterIds,
                        $mediaIds,
                        $request->input('cover_url')
                    );
                    $totalLinkedCount += $count;
                    $itemsCreated++;
                }
            } else if ($request->input('url')) {
                $count = $createAssetAction->execute(
                    null,
                    $request->input('url'),
                    $baseTitle,
                    $request->input('asset_type'),
                    $request->file('cover_image'),
                    $characterIds,
                    $mediaIds,
                    $request->input('cover_url')
                );
                $totalLinkedCount += $count;
                $itemsCreated++;
            }

            return back()->with('success', $itemsCreated . ' recurso(s) creado(s) y vinculado(s) a ' . $totalLinkedCount . ' elementos en el grafo.');

        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
