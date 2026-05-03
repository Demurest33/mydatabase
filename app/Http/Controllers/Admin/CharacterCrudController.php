<?php

namespace App\Http\Controllers\Admin;

use App\Cache\CacheKeys;
use App\DTOs\CharacterDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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

        $tags = $this->loadCharacterTags();

        return view('admin.characters.index', compact('grouped', 'franchiseMedia', 'tags'));
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

        $tags = $this->loadCharacterTags();

        return view('admin.characters.create', compact('mediaList', 'tags'));
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

        $tagIds = array_values(array_filter(array_map('intval', $request->input('tag_ids', []))));
        if (!empty($tagIds)) {
            $client->run(
                'MATCH (c:Character {id: $charId})
                 UNWIND $tagIds AS tagId
                 MATCH (t:Tag {id: tagId})
                 MERGE (c)-[:HAS_TAG]->(t)',
                ['charId' => $id, 'tagIds' => $tagIds]
            );
        }

        CacheKeys::forget(CacheKeys::onCharacterChange());

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

        $selectedTagIds = [];
        foreach ($client->run(
            'MATCH (c:Character {id: $id})-[:HAS_TAG]->(t:Tag) RETURN t.id AS id',
            ['id' => (int) $id]
        ) as $r) {
            $selectedTagIds[] = (int) $r->get('id');
        }

        $tags = $this->loadCharacterTags();

        return view('admin.characters.edit', compact('character', 'mediaList', 'tags', 'selectedTagIds'));
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

        $client->run(
            'MATCH (c:Character {id: $id}) OPTIONAL MATCH (c)-[r:HAS_TAG]->() DELETE r',
            ['id' => (int) $id]
        );

        $tagIds = array_values(array_filter(array_map('intval', $request->input('tag_ids', []))));
        if (!empty($tagIds)) {
            $client->run(
                'MATCH (c:Character {id: $id})
                 UNWIND $tagIds AS tagId
                 MATCH (t:Tag {id: tagId})
                 MERGE (c)-[:HAS_TAG]->(t)',
                ['id' => (int) $id, 'tagIds' => $tagIds]
            );
        }

        CacheKeys::forget(CacheKeys::onCharacterChange());

        return redirect()->route('admin.characters.index')->with('success', 'Character updated successfully.');
    }

    public function getTags(int $id): JsonResponse
    {
        $ids = [];
        foreach ($this->neo4j->client()->run(
            'MATCH (c:Character {id: $id})-[:HAS_TAG]->(t:Tag) RETURN t.id AS id',
            ['id' => $id]
        ) as $r) {
            $ids[] = (int) $r->get('id');
        }
        return response()->json(['tag_ids' => $ids]);
    }

    public function updateTags(Request $request, int $id): JsonResponse
    {
        $tagIds = array_values(array_filter(array_map('intval', $request->input('tag_ids', []))));
        $client = $this->neo4j->client();

        $client->run(
            'MATCH (c:Character {id: $id}) OPTIONAL MATCH (c)-[r:HAS_TAG]->() DELETE r',
            ['id' => $id]
        );

        if (!empty($tagIds)) {
            $client->run(
                'MATCH (c:Character {id: $id})
                 UNWIND $tagIds AS tagId
                 MATCH (t:Tag {id: tagId})
                 MERGE (c)-[:HAS_TAG]->(t)',
                ['id' => $id, 'tagIds' => $tagIds]
            );
        }

        CacheKeys::forget(CacheKeys::onCharacterChange());

        // Return updated tags for the UI
        $tags = [];
        foreach ($client->run(
            'MATCH (c:Character {id: $id})-[:HAS_TAG]->(t:Tag) RETURN t.id AS id, t.name AS name, t.category AS category ORDER BY t.category, t.name',
            ['id' => $id]
        ) as $r) {
            $tags[] = ['id' => (int) $r->get('id'), 'name' => (string) $r->get('name'), 'category' => (string) $r->get('category')];
        }

        return response()->json(['success' => true, 'tags' => $tags, 'count' => count($tags)]);
    }

    private function loadCharacterTags(): array
    {
        $result = $this->neo4j->client()->run(
            'MATCH (t:Tag {type: "character"}) RETURN t ORDER BY t.category ASC, t.name ASC'
        );
        $grouped = [];
        foreach ($result as $record) {
            $d   = $record->get('t')->getProperties()->toArray();
            $cat = (string) ($d['category'] ?? 'General');
            $grouped[$cat][] = [
                'id'   => (int) $d['id'],
                'name' => (string) $d['name'],
            ];
        }
        return $grouped;
    }

    public function destroy($id)
    {
        $client = $this->neo4j->client();
        $client->run(
            'MATCH (c:Character {id: $id}) DETACH DELETE c',
            ['id' => (int) $id]
        );
        CacheKeys::forget(CacheKeys::onCharacterChange());

        return redirect()->route('admin.characters.index')->with('success', 'Character deleted successfully.');
    }
}
