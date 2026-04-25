<?php

namespace App\Http\Controllers;

use App\Actions\GetFranchiseNamesAction;
use App\Cache\CacheKeys;
use App\DTOs\CharacterEdgeDTO;
use App\DTOs\FranchiseDTO;
use App\DTOs\MediaDTO;
use App\DTOs\StudioDTO;
use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $franchises = app(GetFranchiseNamesAction::class)->execute();
        $error      = null;

        $raw = Cache::remember(CacheKeys::franchiseDetail($name), CacheKeys::TTL_SHORT, function () use ($name, &$error) {
            return $this->buildFranchiseDetail($name, $error);
        });

        // Hydrate DTOs from plain arrays — never store DTO objects in cache
        $hydrate  = fn(array $rows) => array_map(fn($r) => MediaDTO::from(
            $r['props'],
            $r['genres'],
            array_map(fn($s) => StudioDTO::from($s), $r['studios']),
            array_map(fn($c) => CharacterEdgeDTO::from($c), $r['characters']),
        ), $rows);

        $root     = $raw['root'] ? MediaDTO::from($raw['root']['props'], $raw['root']['genres'], array_map(fn($s) => StudioDTO::from($s), $raw['root']['studios']), array_map(fn($c) => CharacterEdgeDTO::from($c), $raw['root']['characters'])) : null;
        $timeline = $hydrate($raw['timeline']);
        $source   = $hydrate($raw['source']);
        $others   = $hydrate($raw['others']);

        return view('neo4j.index', compact('franchises', 'root', 'timeline', 'source', 'others', 'error') + ['search' => $name]);
    }

    /** Returns plain arrays only — no DTOs — safe to serialize in any cache driver. */
    private function buildFranchiseDetail(string $name, mixed &$error): array
    {
        $rootRaw = null; $timeline = []; $source = []; $others = [];

        try {
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

                $studiosRaw = array_values(array_map(
                    fn($s) => $s->getProperties()->toArray(),
                    array_filter($record->get('studios')->toArray(), fn($n) => $n !== null)
                ));

                $genreNames = array_values(array_map(
                    fn($g) => $g->getProperties()->toArray()['name'] ?? '',
                    array_filter($record->get('genres')->toArray(), fn($n) => $n !== null)
                ));

                $charsRaw = array_values(array_map(function ($edge) {
                    $p = $edge['node']->getProperties()->toArray();
                    return ['id' => $p['id'] ?? 0, 'name' => $p['name'] ?? '', 'image' => $p['image'] ?? null, 'role' => $edge['role'] ?? 'SUPPORTING'];
                }, array_filter($record->get('characters')->toArray(), fn($e) => $e['node'] !== null)));

                $row = ['props' => $props, 'genres' => $genreNames, 'studios' => $studiosRaw, 'characters' => $charsRaw];

                $tag = $props['tag'] ?? 'main';
                if ($tag === 'source')     $source[]   = $row;
                elseif ($tag === 'other') $others[]   = $row;
                else                      $timeline[] = $row;

                $rootRaw ??= $row;
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }

        return ['root' => $rootRaw, 'timeline' => $timeline, 'source' => $source, 'others' => $others];
    }

    // ── Public franchise catalogue ───────────────────────────────────────────

    public function index(Request $request)
    {
        $letter = $request->input('letter');
        $genre  = $request->input('genre');
        $sort   = $request->input('sort', 'default');

        try {
            $client = $this->neo4j->client();

            // Genres list is stable — cache independently
            $allGenres = Cache::remember('franchises.genres', CacheKeys::TTL_LONG, function () use ($client) {
                $genres = [];
                foreach ($client->run('
                    MATCH (f:Franchise)-[:HAS_ENTRY]->(m:Media)
                    WHERE m.genres IS NOT NULL
                    UNWIND m.genres as g
                    RETURN DISTINCT g ORDER BY g
                ') as $rec) {
                    $genres[] = $rec->get('g');
                }
                return $genres;
            });

            // Franchise list varies by filter combination
            $cacheKey = CacheKeys::FRANCHISES_CATALOGUE . '.' . md5("{$letter}|{$genre}|{$sort}");
            $rows = Cache::remember($cacheKey, CacheKeys::TTL_LONG, function () use ($client, $letter, $genre, $sort) {
                return $this->queryFranchiseCatalogue($client, $letter, $genre, $sort);
            });

            // Hydrate DTOs outside the cache closure — DTOs must never be serialized
            $franchises = array_map(fn($row) => FranchiseDTO::from($row), $rows);

            return view('franchises.index', compact('franchises', 'allGenres', 'letter', 'genre', 'sort'));

        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Returns plain arrays — no DTOs — safe for serialization in any cache driver. */
    private function queryFranchiseCatalogue($client, ?string $letter, ?string $genre, string $sort): array
    {
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

        $rows = [];
        foreach ($client->run($query, $params) as $rec) {
            $rows[] = [
                'name'           => $rec->get('f')->getProperties()->toArray()['name'],
                'mediaCount'     => (int) $rec->get('mediaCount'),
                'characterCount' => (int) $rec->get('charactersCount'),
                'assetCount'     => (int) $rec->get('assetsCount'),
                'coverImage'     => $rec->get('coverImage'),
                'primaryFormat'  => $rec->get('primaryFormat'),
            ];
        }

        return $rows;
    }
}
