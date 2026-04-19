<?php

namespace App\Http\Controllers;

use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

class CharacterController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index(Request $request)
    {
        $characters = [];
        $search = $request->input('search');

        try {
            $client = $this->neo4j->client();
            
            $query = 'MATCH (c:Character) ';
            $params = [];

            if ($search) {
                $query .= 'WHERE c.name CONTAINS $search OR c.id = $search ';
                $params['search'] = $search;
            }

            $query .= 'RETURN c LIMIT 100';
            
            $result = $client->run($query, $params);
            foreach ($result as $record) {
                $characters[] = $record->get('c')->getProperties()->toArray();
            }

        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return view('characters.index', compact('characters', 'search'));
    }

    public function show($id)
    {
        try {
            $client = $this->neo4j->client();

            // 1. Obtener personaje y sus series relacionadas
            $query = '
                MATCH (c:Character {id: $id})
                OPTIONAL MATCH (m:Media)-[r:HAS_CHARACTER]->(c)
                OPTIONAL MATCH (c)-[:HAS_ASSET]->(a:Asset)-[:STORED_IN]->(s:Storage)
                RETURN c, collect(DISTINCT m) as medias, collect(DISTINCT {asset: a, storage: s}) as assets
            ';

            $result = $client->run($query, ['id' => (int)$id]);
            
            if ($result->isEmpty()) {
                abort(404, "Personaje no encontrado");
            }

            $record = $result->first();
            $character = $record->get('c')->getProperties()->toArray();
            
            $medias = array_map(fn($m) => $m->getProperties()->toArray(), 
                array_filter($record->get('medias')->toArray(), fn($m) => $m !== null)
            );

            $assets = array_map(function($item) {
                if ($item['asset'] === null) return null;
                return [
                    'asset' => $item['asset']->getProperties()->toArray(),
                    'storage' => $item['storage'] ? $item['storage']->getProperties()->toArray() : null
                ];
            }, $record->get('assets')->toArray());
            $assets = array_filter($assets);

            return view('characters.show', compact('character', 'medias', 'assets'));

        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    public function storeAsset(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB limit
            'title' => 'nullable|string|max:255'
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            
            $file->storeAs('assets', $filename, 'public');
            
            $assetId = Str::uuid()->toString();
            $title = $request->input('title') ?: pathinfo($originalName, PATHINFO_FILENAME);

            $client = $this->neo4j->client();

            // Crear Nodo Asset y relacionarlo
            $query = '
                MATCH (c:Character {id: $charId})
                MERGE (st:Storage {id: "local_storage"})
                ON CREATE SET st.name = "Local Server", st.type = "LOCAL", st.basePath = "/storage/assets", st.driver = "local"
                
                CREATE (a:Asset {
                    id: $assetId,
                    title: $title,
                    filename: $filename,
                    mimeType: $mimeType,
                    type: $type,
                    createdAt: datetime(),
                    visibility: "public"
                })
                CREATE (c)-[:HAS_ASSET]->(a)
                CREATE (a)-[:STORED_IN]->(st)
                RETURN a
            ';

            $client->run($query, [
                'charId' => (int)$id,
                'assetId' => $assetId,
                'title' => $title,
                'filename' => $filename,
                'mimeType' => $mimeType,
                'type' => strtoupper($extension)
            ]);

            return back()->with('success', 'Asset cargado correctamente');

        } catch (Exception $e) {
            return back()->with('error', 'Error al cargar asset: ' . $e->getMessage());
        }
    }
}
