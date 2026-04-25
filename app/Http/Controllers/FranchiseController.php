<?php

namespace App\Http\Controllers;

use App\Actions\GetFranchiseNamesAction;
use App\DTOs\CharacterEdgeDTO;
use App\DTOs\FranchiseDTO;
use App\DTOs\MediaDTO;
use App\DTOs\StudioDTO;
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

    // ── Public franchise detail ──────────────────────────────────────────────

    public function show(string $name)
    {
        $franchises = [];
        $root       = null;
        $timeline   = [];
        $source     = [];
        $others     = [];
        $error      = null;

        try {
            $franchises = app(GetFranchiseNamesAction::class)->execute();

            $client = $this->neo4j->client();
            $result = $client->run('
                MATCH (f:Franchise {name: $name})-[:HAS_ENTRY]->(m:Media)
                OPTIONAL MATCH (m)-[:PRODUCED_BY]->(s:Studio)
                OPTIONAL MATCH (m)-[:HAS_GENRE]->(g:Genre)
                OPTIONAL MATCH (m)-[rc:HAS_CHARACTER]->(c:Character)
                RETURN m,
                       collect(DISTINCT s)                          as studios,
                       collect(DISTINCT g)                          as genres,
                       collect(DISTINCT {node: c, role: rc.role})   as characters
                ORDER BY m.start_year ASC, m.start_month ASC, m.start_day ASC
            ', ['name' => $name]);

            foreach ($result as $record) {
                $props = $record->get('m')->getProperties()->toArray();

                $studios = array_filter($record->get('studios')->toArray(), fn($n) => $n !== null);
                $genres  = array_filter($record->get('genres')->toArray(),  fn($n) => $n !== null);
                $chars   = array_filter($record->get('characters')->toArray(), fn($e) => $e['node'] !== null);

                $studiosDTOs = array_values(array_map(
                    fn($s) => StudioDTO::from($s->getProperties()->toArray()),
                    $studios
                ));

                $genreNames = array_values(array_map(
                    fn($g) => $g->getProperties()->toArray()['name'] ?? '',
                    $genres
                ));

                $charEdges = array_values(array_map(function ($edge) {
                    $p = $edge['node']->getProperties()->toArray();
                    return CharacterEdgeDTO::from([
                        'id'    => $p['id']    ?? 0,
                        'name'  => $p['name']  ?? '',
                        'image' => $p['image'] ?? null,
                        'role'  => $edge['role'] ?? 'SUPPORTING',
                    ]);
                }, $chars));

                $media = MediaDTO::from($props, $genreNames, $studiosDTOs, $charEdges);

                $tag = $props['tag'] ?? 'main';
                if ($tag === 'source')     $source[]   = $media;
                elseif ($tag === 'other') $others[]   = $media;
                else                      $timeline[] = $media;

                $root ??= $media;
            }

        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }

        // $search kept for backward-compat with the shared neo4j.index view
        return view('neo4j.index', compact('franchises', 'root', 'timeline', 'source', 'others', 'error') + ['search' => $name]);
    }

    // ── Public franchise catalogue ───────────────────────────────────────────

    public function index(Request $request)
    {
        $letter = $request->input('letter');
        $genre  = $request->input('genre');
        $sort   = $request->input('sort', 'default');

        try {
            $client = $this->neo4j->client();

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

            $params       = [];
            $whereClauses = [];

            if ($letter && preg_match('/^[A-Z\#]$/i', $letter)) {
                if ($letter === '#') {
                    $whereClauses[] = 'f.name =~ "^[^a-zA-Z].*"';
                } else {
                    $whereClauses[] = 'toLower(substring(f.name, 0, 1)) = toLower($letter)';
                    $params['letter'] = $letter;
                }
            }

            if ($genre) {
                $whereClauses[] = 'EXISTS { MATCH (f)-[:HAS_ENTRY]->(gm:Media) WHERE $genre IN gm.genres }';
                $params['genre'] = $genre;
            }

            $whereStr = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            $orderStr = match ($sort) {
                'assets_desc' => 'ORDER BY assetsCount DESC, f.name ASC',
                'chars_desc'  => 'ORDER BY charactersCount DESC, f.name ASC',
                default       => 'ORDER BY f.name ASC',
            };

            $query = "
                MATCH (f:Franchise)
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

            $result     = $client->run($query, $params);
            $franchises = [];

            foreach ($result as $rec) {
                $franchises[] = FranchiseDTO::from([
                    'name'           => $rec->get('f')->getProperties()->toArray()['name'],
                    'mediaCount'     => (int) $rec->get('mediaCount'),
                    'characterCount' => (int) $rec->get('charactersCount'),
                    'assetCount'     => (int) $rec->get('assetsCount'),
                    'coverImage'     => $rec->get('coverImage'),
                    'primaryFormat'  => $rec->get('primaryFormat'),
                ]);
            }

            return view('franchises.index', compact('franchises', 'allGenres', 'letter', 'genre', 'sort'));

        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
