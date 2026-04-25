<?php

namespace App\Http\Controllers;

use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Exception;

class Neo4jController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function searchMediaJson(Request $request)
    {
        $search = $request->input('search');
        $franchise = $request->input('franchise');
        
        try {
            $client = $this->neo4j->client();
            
            if ($franchise && $franchise !== 'ALL') {
                $query = 'MATCH (f:Franchise {name: $franchise})-[:HAS_ENTRY]->(m:Media) ';
                $params = ['franchise' => $franchise];
            } else {
                $query = 'MATCH (m:Media) OPTIONAL MATCH (f:Franchise)-[:HAS_ENTRY]->(m) ';
                $params = [];
            }

            $where = [];
            if ($search) {
                // If numeric, check ID, else check title
                if (is_numeric($search)) {
                    $where[] = '(m.id = ' . (int)$search . ')';
                } else {
                    $where[] = '(toLower(m.title) CONTAINS toLower($search) OR toLower(m.native) CONTAINS toLower($search))';
                }
                $params['search'] = $search;
            }

            if (count($where) > 0) {
                $query .= ' WHERE ' . implode(' AND ', $where);
            }

            $query .= '
                OPTIONAL MATCH (m)-[:HAS_ASSET]->(a:Asset)
                WITH m, count(DISTINCT a) as assetCount, collect(DISTINCT f.name) as franchises
                RETURN m, franchises, assetCount
                ORDER BY assetCount DESC, m.start_year DESC, m.title ASC
                LIMIT 50
            ';
            
            $result = $client->run($query, $params);
            
            $mediaList = [];
            foreach ($result as $rec) {
                $m = $rec->get('m')->getProperties()->toArray();
                $m['franchises'] = $rec->get('franchises')->toArray();
                $mediaList[] = $m;
            }

            return response()->json($mediaList);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
