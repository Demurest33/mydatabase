<?php

namespace App\Http\Controllers;

use App\Cache\CacheKeys;
use App\DTOs\AppearanceDTO;
use App\DTOs\AssetDTO;
use App\DTOs\CharacterDTO;
use App\DTOs\MediaDTO;
use App\Services\Neo4jService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Exception;

class CharacterController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    // ── Public index (grouped by franchise / role) ───────────────────────────

    public function index()
    {
        $raw = Cache::remember(CacheKeys::CHARACTERS_GROUPED, CacheKeys::TTL_LONG, function () {
            return $this->buildCharacterGroupsRaw();
        });

        // Hydrate DTOs outside the cache closure — DTOs must never be serialized
        $grouped = [];
        foreach ($raw['grouped'] as $franchise => $roles) {
            foreach ($roles as $role => $chars) {
                $grouped[$franchise][$role] = array_map(fn($d) => CharacterDTO::from($d), $chars);
            }
        }
        $franchiseMedia = [];
        foreach ($raw['franchiseMedia'] as $franchise => $items) {
            $franchiseMedia[$franchise] = array_map(fn($d) => (object) $d, $items);
        }

        return view('characters.index', compact('grouped', 'franchiseMedia'));
    }

    /** Returns plain arrays only — no DTOs — safe to serialize in any cache driver. */
    private function buildCharacterGroupsRaw(): array
    {
        try {
            $client = $this->neo4j->client();

            $result = $client->run(
                'MATCH (c:Character)
                 OPTIONAL MATCH (m:Media)-[r:HAS_CHARACTER]->(c)
                 OPTIONAL MATCH (f:Franchise)-[:HAS_ENTRY]->(m)
                 WITH c,
                      collect(CASE WHEN m IS NOT NULL
                          THEN {mediaId: toString(m.id), mediaTitle: m.title, role: r.role, franchise: f.name}
                          ELSE null END) AS rawApps
                 RETURN c, [a IN rawApps WHERE a IS NOT NULL] AS appearances
                 ORDER BY c.name ASC'
            );

            $roleOrder      = ['MAIN' => 0, 'SUPPORTING' => 1, 'BACKGROUND' => 2];
            $grouped        = [];
            $franchiseMedia = [];

            foreach ($result as $record) {
                $props = $record->get('c')->getProperties()->toArray();
                $apps  = [];

                foreach ($record->get('appearances') as $app) {
                    $franchise = $app['franchise'] ?? null;
                    $mediaId   = (string) ($app['mediaId'] ?? '');
                    if (!$franchise || !$mediaId) continue;

                    $apps[] = [
                        'mediaId'    => $mediaId,
                        'mediaTitle' => (string) ($app['mediaTitle'] ?? ''),
                        'role'       => (string) ($app['role']       ?? 'UNKNOWN'),
                        'franchise'  => (string) $franchise,
                    ];

                    $franchiseMedia[$franchise][$mediaId] ??= [
                        'id'    => $mediaId,
                        'title' => (string) ($app['mediaTitle'] ?? ''),
                    ];
                }

                if (empty($apps)) {
                    $grouped['Sin franquicia']['UNKNOWN'][] = $props;
                    continue;
                }

                usort($apps, fn($a, $b) =>
                    ($roleOrder[$a['role']] ?? 99) <=> ($roleOrder[$b['role']] ?? 99)
                );

                $primary   = $apps[0];
                $charRow   = $props + [
                    'mediaIds'   => array_values(array_unique(array_column($apps, 'mediaId'))),
                    'mediaTitle' => $primary['mediaTitle'],
                    'role'       => $primary['role'],
                ];

                $grouped[$primary['franchise']][$primary['role']][] = $charRow;
            }

            ksort($grouped);
            ksort($franchiseMedia);
            foreach ($franchiseMedia as &$items) {
                usort($items, fn($a, $b) => strcmp($a['title'], $b['title']));
                $items = array_values($items);
            }

            return compact('grouped', 'franchiseMedia');

        } catch (Exception $e) {
            return ['grouped' => [], 'franchiseMedia' => []];
        }
    }

    // ── Character detail ─────────────────────────────────────────────────────

    public function show($id)
    {
        try {
            $raw = Cache::remember(CacheKeys::characterDetail((int) $id), CacheKeys::TTL_SHORT, function () use ($id) {
                return $this->buildCharacterDetailRaw((int) $id);
            });

            if ($raw === null) {
                abort(404, 'Personaje no encontrado');
            }

            $character     = CharacterDTO::from($raw['character']);
            $medias        = array_map(fn($m) => MediaDTO::from($m), $raw['medias']);
            $assets        = array_map(fn($a) => AssetDTO::from($a), $raw['assets']);
            $allCharacters = array_map(fn($c) => CharacterDTO::from($c), $raw['allCharacters']);

            return view('characters.show', compact('character', 'medias', 'assets', 'allCharacters'));

        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    private function buildCharacterDetailRaw(int $id): ?array
    {
        $client = $this->neo4j->client();

        $result = $client->run('
            MATCH (c:Character {id: $id})
            OPTIONAL MATCH (m:Media)-[r:HAS_CHARACTER]->(c)
            OPTIONAL MATCH (c)-[:HAS_ASSET]->(a:Asset)-[:STORED_IN]->(s:Storage)
            RETURN c,
                   collect(DISTINCT m)                       as medias,
                   collect(DISTINCT {asset: a, storage: s}) as assets
        ', ['id' => $id]);

        if ($result->isEmpty()) {
            return null;
        }

        $record = $result->first();

        $medias = array_values(array_filter(array_map(
            fn($m) => $m ? $m->getProperties()->toArray() : null,
            $record->get('medias')->toArray()
        )));

        $assets = array_values(array_filter(array_map(function ($item) {
            if (!$item['asset']) return null;
            $a = $item['asset']->getProperties()->toArray();
            $a['storageName'] = $item['storage']
                ? ($item['storage']->getProperties()->toArray()['name'] ?? null)
                : null;
            return $a;
        }, $record->get('assets')->toArray())));

        $allCharacters = [];
        foreach ($client->run('
            MATCH (c:Character {id: $id})<-[:HAS_CHARACTER]-(:Media)<-[:HAS_ENTRY]-(:Franchise)-[:HAS_ENTRY]->(:Media)-[:HAS_CHARACTER]->(oc:Character)
            WHERE oc.id <> $id
            WITH DISTINCT oc
            OPTIONAL MATCH (:Media)-[r:HAS_CHARACTER]->(oc) WHERE r.role = "MAIN"
            WITH oc, count(r) as mainCount
            RETURN oc, mainCount
            ORDER BY mainCount DESC, oc.name ASC
        ', ['id' => $id]) as $rec) {
            $allCharacters[] = $rec->get('oc')->getProperties()->toArray();
        }

        return [
            'character'     => $record->get('c')->getProperties()->toArray(),
            'medias'        => $medias,
            'assets'        => $assets,
            'allCharacters' => $allCharacters,
        ];
    }

    // ── JSON search endpoint ─────────────────────────────────────────────────

    public function searchJson(Request $request)
    {
        $search    = $request->input('search');
        $franchise = $request->input('franchise');
        $characters = [];

        try {
            $client = $this->neo4j->client();

            $where  = [];
            $params = [];

            if ($search) {
                $where[]          = '(toLower(c.name) CONTAINS toLower($search) OR c.id = $search)';
                $params['search'] = $search;
            }

            if ($franchise && $franchise !== 'ALL') {
                $params['franchise'] = $franchise;
            }

            $query = $franchise && $franchise !== 'ALL'
                ? 'MATCH (f:Franchise {name: $franchise})-[:HAS_ENTRY]->(:Media)-[:HAS_CHARACTER]->(c:Character) '
                : 'MATCH (c:Character) ';

            if ($where) {
                $query .= ' WHERE ' . implode(' AND ', $where);
            }

            $scopedMatch = $franchise && $franchise !== 'ALL'
                ? ' WITH DISTINCT c OPTIONAL MATCH (c)<-[r:HAS_CHARACTER]-(:Media)<-[:HAS_ENTRY]-(allF:Franchise) '
                : ' OPTIONAL MATCH (c)<-[r:HAS_CHARACTER]-(:Media)<-[:HAS_ENTRY]-(allF:Franchise) ';

            $query .= $scopedMatch . '
                OPTIONAL MATCH (c)-[:HAS_ASSET]->(a:Asset)
                WITH c, count(DISTINCT a) as assetCount, collect(DISTINCT allF.name) as franchises, collect(DISTINCT r.role) as roles
                WITH c, assetCount, franchises, CASE WHEN "MAIN" IN roles THEN 1 ELSE 0 END as isMain
                RETURN c, franchises, isMain, assetCount
                ORDER BY assetCount DESC, isMain DESC, c.name ASC
                LIMIT 50
            ';

            foreach ($client->run($query, $params) as $rec) {
                $char = $rec->get('c')->getProperties()->toArray();
                $char['franchises'] = $rec->get('franchises')->toArray();
                $char['isMain']     = $rec->get('isMain');
                $characters[]       = $char;
            }

            return response()->json($characters);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeAsset(Request $request, $id, \App\Actions\CreateAssetAction $createAssetAction)
    {
        $request->validate([
            'file'              => 'nullable|file|max:524288',
            'url'               => 'nullable|url|max:500',
            'title'             => 'nullable|string|max:255',
            'asset_type'        => 'required|string|in:ANIME,MANGA,LIGHT NOVEL,DOUJIN,WALLPAPER ENGINE,IMG,MUSIC,GIF,AMV',
            'cover_image'       => 'nullable|image|max:10240',
            'other_characters'  => 'nullable|array',
            'other_characters.*'=> 'integer',
        ]);

        try {
            $otherCharacters = $request->input('other_characters', []);
            if (!is_array($otherCharacters)) $otherCharacters = [];

            $characterIds = array_values(array_unique(
                array_map('intval', array_merge([$id], $otherCharacters))
            ));

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
