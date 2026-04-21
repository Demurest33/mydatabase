<?php

namespace App\Http\Controllers;

use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

class AssetController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index()
    {
        try {
            $client = $this->neo4j->client();
            
            // Fetch categories (Asset types) and their counts
            $categories = [];
            $resCats = $client->run('MATCH (a:Asset) WHERE a.type IS NOT NULL RETURN a.type as type, count(a) as count ORDER BY count DESC');
            foreach ($resCats as $record) {
                $categories[] = [
                    'name' => $record->get('type'),
                    'count' => $record->get('count')
                ];
            }

            // Fallback default categories if empty
            if (empty($categories)) {
                $defaultTypes = ['IMG', 'GIF', 'VIDEO', 'AMV', 'MUSIC', 'ANIME', 'MANGA', 'LIGHT NOVEL', 'DOUJIN', 'WALLPAPER ENGINE'];
                foreach ($defaultTypes as $dt) {
                    $categories[] = ['name' => $dt, 'count' => 0];
                }
            }

            // Fetch the latest assets
            $assets = [];
            $query = '
                MATCH (a:Asset)
                OPTIONAL MATCH (c:Character)-[:HAS_ASSET]->(a)
                RETURN a, count(c) as charactersCount
                ORDER BY a.createdAt DESC
                LIMIT 50
            ';
            $resAssets = $client->run($query);
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
            'file' => 'nullable|file|max:524288',
            'url' => 'nullable|url|max:500',
            'title' => 'nullable|string|max:255',
            'asset_type' => 'required|string|in:ANIME,MANGA,LIGHT NOVEL,DOUJIN,WALLPAPER ENGINE,IMG,MUSIC,GIF,AMV',
            'cover_image' => 'nullable|image|max:10240',
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

            $count = $createAssetAction->execute(
                $request->file('file'),
                $request->input('url'),
                $request->input('title'),
                $request->input('asset_type'),
                $request->file('cover_image'),
                $characterIds,
                $mediaIds
            );

            return back()->with('success', 'Recurso centralizado creado y vinculado a ' . $count . ' elementos en el grafo.');

        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
