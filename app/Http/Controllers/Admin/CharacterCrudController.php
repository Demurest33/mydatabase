<?php

namespace App\Http\Controllers\Admin;

use App\Cache\CacheKeys;
use App\DTOs\CharacterDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Neo4jService;
use Illuminate\Support\Facades\Cache;
use Exception;

class CharacterCrudController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index()
    {
        $raw = Cache::remember(CacheKeys::ADMIN_CHARACTERS_GROUPED, CacheKeys::TTL_LONG, function () {
            return $this->buildRaw();
        });

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

        return view('admin.characters.index', compact('grouped', 'franchiseMedia'));
    }

    private function buildRaw(): array
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
                        'role'       => (string) ($app['role'] ?? 'UNKNOWN'),
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

                $primary = $apps[0];
                $grouped[$primary['franchise']][$primary['role']][] = $props + [
                    'mediaIds'   => array_values(array_unique(array_column($apps, 'mediaId'))),
                    'mediaTitle' => $primary['mediaTitle'],
                    'role'       => $primary['role'],
                ];
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

    public function create()
    {
        $client = $this->neo4j->client();
        $result = $client->run('MATCH (m:Media) RETURN m.id as id, m.title as title ORDER BY m.title ASC');
        $mediaList = [];
        foreach ($result as $record) {
            $mediaList[] = [
                'id' => $record->get('id'),
                'title' => $record->get('title')
            ];
        }

        return view('admin.characters.create', compact('mediaList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|url|max:500',
            'media_id' => 'required|integer',
            'role' => 'required|string|in:MAIN,SUPPORTING,BACKGROUND',
        ]);

        $client = $this->neo4j->client();
        
        $id = rand(1000000, 9999999);

        $params = [
            'id' => $id,
            'name' => $request->input('name'),
            'image' => $request->input('image', ''),
            'media_id' => (int) $request->input('media_id'),
            'role' => $request->input('role')
        ];

        $query = '
            MATCH (m:Media {id: $media_id})
            CREATE (c:Character {
                id: $id,
                name: $name,
                image: $image
            })
            CREATE (m)-[:HAS_CHARACTER {role: $role}]->(c)
        ';

        $client->run($query, $params);
        Cache::forgetMultiple(CacheKeys::onCharacterChange());

        return redirect()->route('admin.characters.index')->with('success', 'Character created successfully.');
    }

    public function edit($id)
    {
        $client = $this->neo4j->client();
        $result = $client->run(
            'MATCH (c:Character {id: $id})
             OPTIONAL MATCH (m:Media)-[r:HAS_CHARACTER]->(c)
             RETURN c, r.role AS role, m.id AS mediaId, m.title AS mediaTitle',
            ['id' => (int) $id]
        );

        $record = $result->first();
        if (!$record) {
            return redirect()->route('admin.characters.index')->with('error', 'Character not found.');
        }

        $character = $record->get('c')->getProperties()->toArray();
        $character['role']       = $record->get('role');
        $character['mediaId']    = $record->get('mediaId');
        $character['mediaTitle'] = $record->get('mediaTitle');

        $mediaResult = $client->run('MATCH (m:Media) RETURN m.id as id, m.title as title ORDER BY m.title ASC');
        $mediaList = [];
        foreach ($mediaResult as $r) {
            $mediaList[] = ['id' => $r->get('id'), 'title' => $r->get('title')];
        }

        return view('admin.characters.edit', compact('character', 'mediaList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'image'    => 'nullable|url|max:500',
            'media_id' => 'required|integer',
            'role'     => 'required|string|in:MAIN,SUPPORTING,BACKGROUND',
        ]);

        $client = $this->neo4j->client();
        $client->run(
            'MATCH (c:Character {id: $id})
             SET c.name = $name, c.image = $image
             WITH c
             OPTIONAL MATCH (old_m:Media)-[old_r:HAS_CHARACTER]->(c)
             DELETE old_r
             WITH c
             MATCH (new_m:Media {id: $media_id})
             MERGE (new_m)-[:HAS_CHARACTER {role: $role}]->(c)',
            [
                'id'       => (int) $id,
                'name'     => $request->input('name'),
                'image'    => $request->input('image', ''),
                'media_id' => (int) $request->input('media_id'),
                'role'     => $request->input('role'),
            ]
        );

        Cache::forgetMultiple(CacheKeys::onCharacterChange());

        return redirect()->route('admin.characters.index')->with('success', 'Character updated successfully.');
    }

    public function destroy($id)
    {
        $client = $this->neo4j->client();
        $client->run(
            'MATCH (c:Character {id: $id}) DETACH DELETE c',
            ['id' => (int) $id]
        );
        Cache::forgetMultiple(CacheKeys::onCharacterChange());

        return redirect()->route('admin.characters.index')->with('success', 'Character deleted successfully.');
    }
}
