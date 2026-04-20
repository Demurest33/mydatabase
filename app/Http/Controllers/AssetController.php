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

            // Obtener todas las franquicias para el filtro
            $franchises = [];
            $res = $client->run('MATCH (f:Franchise) RETURN f.name as name ORDER BY f.name ASC');
            foreach ($res as $record) {
                $franchises[] = $record->get('name');
            }

            return view('assets.create', compact('franchises'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|max:524288',
            'url' => 'nullable|url|max:500',
            'title' => 'nullable|string|max:255',
            'asset_type' => 'required|string|in:ANIME,MANGA,LIGHT NOVEL,DOUJIN,WALLPAPER ENGINE,IMG,MUSIC,GIF,AMV',
            'cover_image' => 'nullable|image|max:10240',
            'characters' => 'required|array|min:1',
            'characters.*' => 'integer'
        ]);

        try {
            $client = $this->neo4j->client();
            $assetId = Str::uuid()->toString();
            $title = $request->input('title');
            $assetType = $request->input('asset_type');
            
            $filename = null;
            $url = $request->input('url');
            $mimeType = 'text/html';
            $coverFilename = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                
                $file->storeAs('assets', $filename, 'public');
                if (!$title) $title = pathinfo($originalName, PATHINFO_FILENAME);
            } else if ($url) {
                if (!$title) $title = parse_url($url, PHP_URL_HOST) ?: 'Enlace Externo';
            } else {
                return back()->with('error', 'Debes proporcionar un archivo o una URL válida.');
            }

            if ($request->hasFile('cover_image')) {
                $coverFile = $request->file('cover_image');
                $coverExt = $coverFile->getClientOriginalExtension();
                $coverOriginalName = $coverFile->getClientOriginalName();
                $coverFilename = time() . '_cover_' . Str::slug(pathinfo($coverOriginalName, PATHINFO_FILENAME)) . '.' . $coverExt;
                $coverFile->storeAs('assets/covers', $coverFilename, 'public');
            }

            // Preparar lista de IDs casteados rigurosamente a int
            $characterIds = array_values(array_unique(array_map('intval', $request->input('characters', []))));

            // Crear Nodo Asset y relacionarlo con TODOS los personajes
            $query = '
                MERGE (st:Storage {id: $storageId})
                ON CREATE SET st.name = $storageName, st.type = $storageType, st.basePath = $basePath, st.driver = $driver
                
                CREATE (a:Asset {
                    id: $assetId,
                    title: $title,
                    filename: $filename,
                    coverFilename: $coverFilename,
                    url: $url,
                    mimeType: $mimeType,
                    type: $assetType,
                    createdAt: datetime(),
                    visibility: "public"
                })
                CREATE (a)-[:STORED_IN]->(st)
                
                WITH a, $characterIds as listIds
                UNWIND listIds as charId
                MATCH (c:Character {id: charId})
                CREATE (c)-[:HAS_ASSET]->(a)
                RETURN count(c)
            ';

            $client->run($query, [
                'characterIds' => $characterIds,
                'assetId' => $assetId,
                'title' => $title,
                'filename' => $filename,
                'coverFilename' => $coverFilename,
                'url' => $url,
                'mimeType' => $mimeType,
                'assetType' => $assetType,
                'storageId' => $filename ? "local_storage" : "web_storage",
                'storageName' => $filename ? "Local Server" : "External Web",
                'storageType' => $filename ? "LOCAL" : "REMOTE",
                'basePath' => $filename ? "/storage/assets" : "",
                'driver' => $filename ? "local" : "url"
            ]);

            return back()->with('success', 'Recurso centralizado creado y vinculado a ' . count($characterIds) . ' personajes.');

        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
