<?php

namespace App\Http\Controllers;

use App\Actions\GetFranchiseNamesAction;
use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Exception;

class FranchiseController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function show(string $name)
    {
        $franchises    = [];
        $franchiseData = null;
        $error         = null;

        try {
            $franchises = app(\App\Actions\GetFranchiseNamesAction::class)->execute();

            $client = $this->neo4j->client();
            $result = $client->run('
                MATCH (f:Franchise {name: $name})-[:HAS_ENTRY]->(m:Media)
                OPTIONAL MATCH (m)-[:PRODUCED_BY]->(s:Studio)
                OPTIONAL MATCH (m)-[:HAS_GENRE]->(g:Genre)
                OPTIONAL MATCH (m)-[rc:HAS_CHARACTER]->(c:Character)
                RETURN m,
                       collect(DISTINCT s) as studios,
                       collect(DISTINCT g) as genres,
                       collect(DISTINCT {node: c, role: rc.role}) as characters
                ORDER BY m.start_year ASC, m.start_month ASC, m.start_day ASC
            ', ['name' => $name]);

            $timeline = [];
            $source   = [];
            $others   = [];
            $root     = null;

            foreach ($result as $record) {
                $props         = $record->get('m')->getProperties()->toArray();
                $studiosNodes  = array_filter($record->get('studios')->toArray(), fn($n) => $n !== null);
                $genresNodes   = array_filter($record->get('genres')->toArray(), fn($n) => $n !== null);
                $characterEdges = array_filter($record->get('characters')->toArray(), fn($e) => $e['node'] !== null);

                $item = [
                    'id'           => $props['id'],
                    'title'        => ['romaji' => $props['title'] ?? 'N/A', 'native' => $props['native'] ?? 'N/A'],
                    'description'  => $props['description'] ?? '',
                    'format'       => $props['format'] ?? '',
                    'status'       => $props['status'] ?? '',
                    'type'         => $props['type'] ?? '',
                    'averageScore' => $props['score'] ?? 0,
                    'season'       => $props['season'] ?? '',
                    'seasonYear'   => $props['year'] ?? '',
                    'coverImage'   => ['large' => $props['coverImage'] ?? ''],
                    'bannerImage'  => $props['bannerImage'] ?? '',
                    'startDate'    => ['year' => $props['start_year'] ?? null, 'month' => $props['start_month'] ?? null, 'day' => $props['start_day'] ?? null],
                    'genres'       => array_map(fn($g) => $g->getProperties()->toArray()['name'], $genresNodes),
                    'studios'      => ['nodes' => array_map(fn($s) => $s->getProperties()->toArray(), $studiosNodes)],
                    'characters'   => ['edges' => array_map(function ($edge) {
                        $p = $edge['node']->getProperties()->toArray();
                        return ['role' => $edge['role'], 'node' => ['id' => $p['id'] ?? null, 'name' => ['full' => $p['name']], 'image' => ['large' => $p['image'] ?? '']]];
                    }, $characterEdges)],
                ];

                $tag = $props['tag'] ?? 'main';
                if ($tag === 'source')     $source[]   = $item;
                elseif ($tag === 'other') $others[]   = $item;
                else                      $timeline[] = $item;

                if ($root === null) $root = $item;
            }

            $franchiseData = compact('timeline', 'source', 'others', 'root');

        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }

        return view('neo4j.index', [
            'franchises'    => $franchises,
            'franchiseData' => $franchiseData,
            'search'        => $name,
            'error'         => $error,
        ]);
    }

    public function index(Request $request)
    {
        $letter = $request->input('letter');
        $genre = $request->input('genre');
        $sort = $request->input('sort', 'default');
        
        try {
            $client = $this->neo4j->client();

            // 1. Fetch available Genres for the dropdown. 
            // We find all Media that belongs to a Franchise, UNWIND their genres and collect distinct.
            $genresResult = $client->run('
                MATCH (f:Franchise)-[:HAS_ENTRY]->(m:Media)
                WHERE m.genres IS NOT NULL
                UNWIND m.genres as g
                RETURN DISTINCT g ORDER BY g
            ');
            $allGenres = [];
            foreach ($genresResult as $rec) {
                $allGenres[] = $rec->get('g');
            }

            // 2. Build Query
            $params = [];
            $matchClause = 'MATCH (f:Franchise)';
            $whereClauses = [];

            if ($letter && preg_match('/^[A-Z\#]$/i', $letter)) {
                if ($letter === '#') {
                    // Start with a number or symbol
                    $whereClauses[] = 'f.name =~ "^[^a-zA-Z].*"';
                } else {
                    $whereClauses[] = 'toLower(substring(f.name, 0, 1)) = toLower($letter)';
                    $params['letter'] = $letter;
                }
            }

            if ($genre) {
                // Must have at least one media with this genre
                $whereClauses[] = 'EXISTS { MATCH (f)-[:HAS_ENTRY]->(gm:Media) WHERE $genre IN gm.genres }';
                $params['genre'] = $genre;
            }

            $whereStr = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            // Order By logic
            // We can sort by name, mediaCount, characterCount
            $orderStr = 'ORDER BY f.name ASC';
            if ($sort === 'assets_desc') {
                $orderStr = 'ORDER BY assetsCount DESC, f.name ASC';
            } elseif ($sort === 'chars_desc') {
                $orderStr = 'ORDER BY charactersCount DESC, f.name ASC';
            }

            $query = "
                $matchClause
                $whereStr
                OPTIONAL MATCH (f)-[:HAS_ENTRY]->(m:Media)
                OPTIONAL MATCH (m)-[:HAS_CHARACTER]->(c:Character)
                OPTIONAL MATCH (c)-[:HAS_ASSET]->(a:Asset)
                WITH f, 
                     count(DISTINCT m) as mediaCount, 
                     count(DISTINCT c) as charactersCount,
                     count(DISTINCT a) as assetsCount,
                     coalesce(f.image, collect(DISTINCT m.coverImage)[0]) as coverImage,
                     collect(DISTINCT m.format)[0] as primaryFormat
                RETURN f, mediaCount, charactersCount, assetsCount, coverImage, primaryFormat
                $orderStr
                LIMIT 100
            ";

            $result = $client->run($query, $params);

            $franchises = [];
            foreach ($result as $rec) {
                $f = $rec->get('f')->getProperties()->toArray();
                $f['mediaCount'] = $rec->get('mediaCount');
                $f['charactersCount'] = $rec->get('charactersCount');
                $f['assetsCount'] = $rec->get('assetsCount');
                $f['coverImage'] = $rec->get('coverImage');
                $f['primaryFormat'] = $rec->get('primaryFormat'); // e.g. "TV", "MANGA"
                
                $franchises[] = $f;
            }

            return view('franchises.index', compact('franchises', 'allGenres', 'letter', 'genre', 'sort'));

        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
