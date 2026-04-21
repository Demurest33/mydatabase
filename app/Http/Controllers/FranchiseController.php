<?php

namespace App\Http\Controllers;

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
