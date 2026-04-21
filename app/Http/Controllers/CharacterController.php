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

    public function searchJson(Request $request)
    {
        $search = $request->input('search');
        $franchise = $request->input('franchise');
        $characters = [];

        try {
            $client = $this->neo4j->client();
            
            $query = '
                MATCH (c:Character)
                OPTIONAL MATCH (c)<-[r:HAS_CHARACTER]-(:Media)<-[:HAS_ENTRY]-(f:Franchise)
            ';
            
            $where = [];
            $params = [];

            if ($search) {
                $where[] = '(toLower(c.name) CONTAINS toLower($search) OR c.id = $search)';
                $params['search'] = $search;
            }

            if ($franchise && $franchise !== 'ALL') {
                $where[] = 'f.name = $franchise';
                $params['franchise'] = $franchise;
            }

            if (count($where) > 0) {
                $query .= ' WHERE ' . implode(' AND ', $where);
            }

            $query .= '
                WITH c, collect(DISTINCT f.name) as franchises, collect(DISTINCT r.role) as roles
                WITH c, franchises, CASE WHEN "MAIN" IN roles THEN 1 ELSE 0 END as isMain
                RETURN c, franchises, isMain
                ORDER BY isMain DESC, c.name ASC
                LIMIT 50
            ';
            
            $result = $client->run($query, $params);
            
            foreach ($result as $rec) {
                $char = $rec->get('c')->getProperties()->toArray();
                $char['franchises'] = $rec->get('franchises')->toArray();
                $char['isMain'] = $rec->get('isMain');
                $characters[] = $char;
            }

            return response()->json($characters);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

    public function storeAsset(Request $request, $id, \App\Actions\CreateAssetAction $createAssetAction)
    {
        $request->validate([
            'file' => 'nullable|file|max:524288',
            'url' => 'nullable|url|max:500',
            'title' => 'nullable|string|max:255',
            'asset_type' => 'required|string|in:ANIME,MANGA,LIGHT NOVEL,DOUJIN,WALLPAPER ENGINE,IMG,MUSIC,GIF,AMV',
            'cover_image' => 'nullable|image|max:10240',
            'other_characters' => 'nullable|array',
            'other_characters.*' => 'integer'
        ]);

        try {
            $otherCharacters = $request->input('other_characters', []);
            if (!is_array($otherCharacters)) $otherCharacters = [];
            
            $characterIds = array_values(array_unique(array_map('intval', array_merge([$id], $otherCharacters))));

            $count = $createAssetAction->execute(
                $request->file('file'),
                $request->input('url'),
                $request->input('title'),
                $request->input('asset_type'),
                $request->file('cover_image'),
                $characterIds
            );

            return back()->with('success', 'Recurso guardado y vinculado a ' . $count . ' personajes.');

        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
