<?php

namespace App\Http\Controllers\Admin;

use App\Cache\CacheKeys;
use App\DTOs\AlbumDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Neo4jService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AlbumCrudController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index()
    {
        $raw = Cache::remember(CacheKeys::ADMIN_ALBUMS_INDEX, CacheKeys::TTL_LONG, function () {
            $client = $this->neo4j->client();
            $result = $client->run(
                'MATCH (a:Album)
                 OPTIONAL MATCH (a)-[:INCLUDES_MEDIA]->(m:Media)
                 OPTIONAL MATCH (m)-[:HAS_CHARACTER]->(c:Character)
                 RETURN a,
                        count(DISTINCT m) AS mediaCount,
                        count(DISTINCT c) AS characterCount
                 ORDER BY a.name ASC'
            );

            $albums = [];
            foreach ($result as $record) {
                $props = $record->get('a')->getProperties()->toArray();
                $albums[] = [
                    'props'          => $props,
                    'mediaCount'     => (int) $record->get('mediaCount'),
                    'characterCount' => (int) $record->get('characterCount'),
                ];
            }
            return $albums;
        });

        $albums = array_map(
            fn($r) => AlbumDTO::from($r['props'], $r['mediaCount'], $r['characterCount']),
            $raw
        );

        return view('admin.albums.index', compact('albums'));
    }

    public function create()
    {
        $mediaByFranchise = $this->getMediaGroupedByFranchise();
        return view('admin.albums.create', compact('mediaByFranchise'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'coverImage'  => 'nullable|url|max:500',
            'media_ids'   => 'nullable|array',
            'media_ids.*' => 'integer',
        ]);

        $id       = rand(1000000, 9999999);
        $slug     = Str::slug($request->input('name'));
        $mediaIds = array_map('intval', $request->input('media_ids', []));

        $client = $this->neo4j->client();

        $client->run(
            'CREATE (a:Album {
                id:          $id,
                name:        $name,
                slug:        $slug,
                description: $description,
                coverImage:  $coverImage,
                isActive:    true
            })',
            [
                'id'          => $id,
                'name'        => $request->input('name'),
                'slug'        => $slug,
                'description' => $request->input('description', ''),
                'coverImage'  => $request->input('coverImage', ''),
            ]
        );

        if (!empty($mediaIds)) {
            $client->run(
                'MATCH (a:Album {id: $albumId})
                 UNWIND $mediaIds AS mid
                 MATCH (m:Media {id: mid})
                 MERGE (a)-[:INCLUDES_MEDIA]->(m)',
                ['albumId' => $id, 'mediaIds' => $mediaIds]
            );
        }

        CacheKeys::forget(CacheKeys::onAlbumChange($id));

        return redirect()->route('admin.albums.index')->with('success', 'Album created.');
    }

    public function edit($id)
    {
        $client = $this->neo4j->client();
        $result = $client->run(
            'MATCH (a:Album {id: $id})
             OPTIONAL MATCH (a)-[:INCLUDES_MEDIA]->(m:Media)
             RETURN a, collect(m.id) AS selectedMediaIds',
            ['id' => (int) $id]
        );

        $record = $result->first();
        if (!$record) {
            return redirect()->route('admin.albums.index')->with('error', 'Album not found.');
        }

        $album            = $record->get('a')->getProperties()->toArray();
        $selectedMediaIds = $record->get('selectedMediaIds')->toArray();
        $mediaByFranchise = $this->getMediaGroupedByFranchise();

        return view('admin.albums.edit', compact('album', 'selectedMediaIds', 'mediaByFranchise'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'coverImage'  => 'nullable|url|max:500',
            'media_ids'   => 'nullable|array',
            'media_ids.*' => 'integer',
            'isActive'    => 'nullable|boolean',
        ]);

        $mediaIds = array_map('intval', $request->input('media_ids', []));
        $slug     = Str::slug($request->input('name'));

        $client = $this->neo4j->client();

        $client->run(
            'MATCH (a:Album {id: $id})
             SET a.name        = $name,
                 a.slug        = $slug,
                 a.description = $description,
                 a.coverImage  = $coverImage,
                 a.isActive    = $isActive
             WITH a
             OPTIONAL MATCH (a)-[r:INCLUDES_MEDIA]->()
             DELETE r',
            [
                'id'          => (int) $id,
                'name'        => $request->input('name'),
                'slug'        => $slug,
                'description' => $request->input('description', ''),
                'coverImage'  => $request->input('coverImage', ''),
                'isActive'    => $request->boolean('isActive', true),
            ]
        );

        if (!empty($mediaIds)) {
            $client->run(
                'MATCH (a:Album {id: $albumId})
                 UNWIND $mediaIds AS mid
                 MATCH (m:Media {id: mid})
                 MERGE (a)-[:INCLUDES_MEDIA]->(m)',
                ['albumId' => (int) $id, 'mediaIds' => $mediaIds]
            );
        }

        CacheKeys::forget(CacheKeys::onAlbumChange((int) $id));

        return redirect()->route('admin.albums.index')->with('success', 'Album updated.');
    }

    public function destroy($id)
    {
        $client = $this->neo4j->client();
        $client->run(
            'MATCH (a:Album {id: $id}) DETACH DELETE a',
            ['id' => (int) $id]
        );
        CacheKeys::forget(CacheKeys::onAlbumChange((int) $id));

        return redirect()->route('admin.albums.index')->with('success', 'Album deleted.');
    }

    private function getMediaGroupedByFranchise(): array
    {
        $client = $this->neo4j->client();
        $result = $client->run(
            'MATCH (m:Media)
             OPTIONAL MATCH (f:Franchise)-[:HAS_ENTRY]->(m)
             RETURN m.id AS id, m.title AS title, m.format AS format,
                    m.coverImage AS coverImage, f.name AS franchise
             ORDER BY franchise ASC, m.title ASC'
        );

        $grouped = [];
        foreach ($result as $record) {
            $franchise = $record->get('franchise') ?? 'Sin franquicia';
            $grouped[$franchise][] = [
                'id'         => (int) $record->get('id'),
                'title'      => $record->get('title'),
                'format'     => $record->get('format'),
                'coverImage' => $record->get('coverImage'),
            ];
        }
        return $grouped;
    }
}
