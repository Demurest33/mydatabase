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

            // 2. Obtener personajes PRIORIZANDO los de la misma franquicia
            $allCharacters = [];
            $charQuery = '
                MATCH (oc:Character)
                WHERE oc.id <> $id
                OPTIONAL MATCH path = (this:Character {id: $id})<-[:HAS_CHARACTER]-(:Media)<-[:HAS_ENTRY]-(:Franchise)-[:HAS_ENTRY]->(:Media)-[:HAS_CHARACTER]->(oc)
                WITH oc, CASE WHEN path IS NOT NULL THEN 1 ELSE 0 END as p
                WITH oc, max(p) as priority
                OPTIONAL MATCH (:Media)-[r:HAS_CHARACTER]->(oc) WHERE r.role = "MAIN"
                WITH oc, priority, count(r) as mainCount
                RETURN oc, priority, mainCount
                ORDER BY priority DESC, mainCount DESC, oc.name ASC
            ';
            
            $charResult = $client->run($charQuery, ['id' => (int)$id]);
            foreach ($charResult as $rec) {
                $charData = $rec->get('oc')->getProperties()->toArray();
                $charData['priority'] = $rec->get('priority');
                $allCharacters[] = $charData;
            }

            return view('characters.show', compact('character', 'medias', 'assets', 'allCharacters'));

        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    public function storeAsset(Request $request, $id)
    {
        $request->validate([
            'file' => 'nullable|file|max:20480',
            'url' => 'nullable|url|max:500',
            'title' => 'nullable|string|max:255',
            'other_characters' => 'nullable|array',
            'other_characters.*' => 'integer'
        ]);

        try {
            $client = $this->neo4j->client();
            $assetId = Str::uuid()->toString();
            $title = $request->input('title');
            
            $filename = null;
            $url = $request->input('url');
            $type = 'URL';
            $mimeType = 'text/html';

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                
                $file->storeAs('assets', $filename, 'public');
                $type = strtoupper($extension);
                if (!$title) $title = pathinfo($originalName, PATHINFO_FILENAME);
            } else if ($url) {
                if (!$title) $title = parse_url($url, PHP_URL_HOST) ?: 'Enlace Externo';
            } else {
                return back()->with('error', 'Debes proporcionar un archivo o una URL válida.');
            }

            // Preparar lista de IDs de personajes (el actual + los extras, casteados a entero rigurosamente)
            $otherCharacters = $request->input('other_characters', []);
            if (!is_array($otherCharacters)) $otherCharacters = [];
            
            $characterIds = array_values(array_unique(array_map('intval', array_merge([$id], $otherCharacters))));

            // Crear Nodo Asset y relacionarlo con TODOS los personajes
            $query = '
                MERGE (st:Storage {id: $storageId})
                ON CREATE SET st.name = $storageName, st.type = $storageType, st.basePath = $basePath, st.driver = $driver
                
                CREATE (a:Asset {
                    id: $assetId,
                    title: $title,
                    filename: $filename,
                    url: $url,
                    mimeType: $mimeType,
                    type: $type,
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
                'url' => $url,
                'mimeType' => $mimeType,
                'type' => $type,
                'storageId' => $filename ? "local_storage" : "web_storage",
                'storageName' => $filename ? "Local Server" : "External Web",
                'storageType' => $filename ? "LOCAL" : "REMOTE",
                'basePath' => $filename ? "/storage/assets" : "",
                'driver' => $filename ? "local" : "url"
            ]);

            return back()->with('success', 'Recurso guardado y vinculado a ' . count($characterIds) . ' personajes.');

        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
