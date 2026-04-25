<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Neo4jService;

class CharacterCrudController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index()
    {
        $client = $this->neo4j->client();

        // Aggregate all media appearances per character to avoid duplicates
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
        $franchiseMedia = []; // franchise → [mediaId => {id, title}]

        foreach ($result as $record) {
            $char = $record->get('c')->getProperties()->toArray();
            $apps = [];

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

                // Build sidebar lookup (deduplicated by media ID)
                $franchiseMedia[$franchise][$mediaId] ??= [
                    'id'    => $mediaId,
                    'title' => (string) ($app['mediaTitle'] ?? ''),
                ];
            }

            if (empty($apps)) {
                $char['mediaIds'] = [];
                $grouped['Sin franquicia']['UNKNOWN'][] = $char;
                continue;
            }

            // Primary appearance = highest-priority role
            usort($apps, fn($a, $b) =>
                ($roleOrder[$a['role']] ?? 99) <=> ($roleOrder[$b['role']] ?? 99)
            );

            $primary           = $apps[0];
            $char['mediaIds']  = array_values(array_unique(array_column($apps, 'mediaId')));
            $char['mediaTitle'] = $primary['mediaTitle'];

            $grouped[$primary['franchise']][$primary['role']][] = $char;
        }

        ksort($grouped);
        ksort($franchiseMedia);
        foreach ($franchiseMedia as &$items) {
            usort($items, fn($a, $b) => strcmp($a['title'], $b['title']));
            $items = array_values($items);
        }

        return view('admin.characters.index', compact('grouped', 'franchiseMedia'));
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

        return redirect()->route('admin.characters.index')->with('success', 'Character updated successfully.');
    }

    public function destroy($id)
    {
        $client = $this->neo4j->client();
        $client->run(
            'MATCH (c:Character {id: $id}) DETACH DELETE c',
            ['id' => (int) $id]
        );

        return redirect()->route('admin.characters.index')->with('success', 'Character deleted successfully.');
    }
}
