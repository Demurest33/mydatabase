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

    public function index(Request $request)
    {
        $franchises = [];
        $error = null;
        $franchiseData = null;
        $search = $request->input('franchise');

        try {
            $franchises = app(\App\Actions\GetFranchiseNamesAction::class)->execute();

            if ($search) {
                $client = $this->neo4j->client();
                $query = '
                    MATCH (f:Franchise {name: $name})-[:HAS_ENTRY]->(m:Media)
                    OPTIONAL MATCH (m)-[:PRODUCED_BY]->(s:Studio)
                    OPTIONAL MATCH (m)-[:HAS_GENRE]->(g:Genre)
                    OPTIONAL MATCH (m)-[rc:HAS_CHARACTER]->(c:Character)
                    RETURN m, 
                           collect(DISTINCT s) as studios, 
                           collect(DISTINCT g) as genres, 
                           collect(DISTINCT {node: c, role: rc.role}) as characters
                    ORDER BY m.start_year ASC, m.start_month ASC, m.start_day ASC
                ';
                
                $graphResult = $client->run($query, ['name' => $search]);

                $timeline = [];
                $source = [];
                $others = [];
                $root = null;

                foreach ($graphResult as $record) {
                    $mNode = $record->get('m');
                    $props = $mNode->getProperties()->toArray();
                    
                    // Filtrar studios nulos
                    $studiosNodes = array_filter($record->get('studios')->toArray(), fn($node) => $node !== null);
                    // Filtrar géneros nulos
                    $genresNodes = array_filter($record->get('genres')->toArray(), fn($node) => $node !== null);
                    // Filtrar personajes nulos (vienen como array con key 'node')
                    $characterEdges = array_filter($record->get('characters')->toArray(), fn($edge) => $edge['node'] !== null);

                    $item = [
                        'id' => $props['id'],
                        'title' => [
                            'romaji' => $props['title'] ?? 'N/A',
                            'native' => $props['native'] ?? 'N/A'
                        ],
                        'description' => $props['description'] ?? '',
                        'format' => $props['format'] ?? '',
                        'status' => $props['status'] ?? '',
                        'type' => $props['type'] ?? '',
                        'averageScore' => $props['score'] ?? 0,
                        'season' => $props['season'] ?? '',
                        'seasonYear' => $props['year'] ?? '',
                        'coverImage' => ['large' => $props['coverImage'] ?? ''],
                        'bannerImage' => $props['bannerImage'] ?? '',
                        'startDate' => [
                            'year' => $props['start_year'] ?? null,
                            'month' => $props['start_month'] ?? null,
                            'day' => $props['start_day'] ?? null,
                        ],
                        'genres' => array_map(fn($g) => $g->getProperties()->toArray()['name'], $genresNodes),
                        'studios' => [
                            'nodes' => array_map(fn($s) => $s->getProperties()->toArray(), $studiosNodes)
                        ],
                        'characters' => [
                            'edges' => array_map(function($charEdge) {
                                $cNode = $charEdge['node'];
                                $cProps = $cNode->getProperties()->toArray();
                                return [
                                    'role' => $charEdge['role'],
                                    'node' => [
                                        'name' => ['full' => $cProps['name']],
                                        'image' => ['large' => $cProps['image'] ?? '']
                                    ]
                                ];
                            }, $characterEdges)
                        ]
                    ];

                    $tag = $props['tag'] ?? 'main';
                    if ($tag === 'source') $source[] = $item;
                    elseif ($tag === 'other') $others[] = $item;
                    else $timeline[] = $item;

                    if ($root === null) $root = $item;
                }

                $franchiseData = [
                    'timeline' => $timeline,
                    'source' => $source,
                    'others' => $others,
                    'root' => $root
                ];
            }

        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }

        return view('neo4j.index', compact('franchises', 'error', 'franchiseData', 'search'));
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
