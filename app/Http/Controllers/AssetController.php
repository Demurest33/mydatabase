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

            // Obtener personajes con su info de franquicia y si son MAIN
            $characters = [];
            $query = '
                MATCH (c:Character)
                OPTIONAL MATCH (c)<-[r:HAS_CHARACTER]-(:Media)<-[:HAS_ENTRY]-(f:Franchise)
                WITH c, collect(DISTINCT f.name) as franchises, collect(DISTINCT r.role) as roles
                WITH c, franchises, CASE WHEN "MAIN" IN roles THEN 1 ELSE 0 END as isMain
                RETURN c, franchises, isMain
                ORDER BY isMain DESC, c.name ASC
            ';

            $result = $client->run($query);
            foreach ($result as $rec) {
                $char = $rec->get('c')->getProperties()->toArray();
                $char['franchises'] = $rec->get('franchises')->toArray();
                $char['isMain'] = $rec->get('isMain');
                $characters[] = $char;
            }

            return view('assets.create', compact('characters', 'franchises'));
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
            'characters' => 'required|array|min:1',
            'characters.*' => 'integer'
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

            return back()->with('success', 'Recurso centralizado creado y vinculado a ' . count($characterIds) . ' personajes.');

        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
