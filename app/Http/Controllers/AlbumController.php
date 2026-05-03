<?php

namespace App\Http\Controllers;

use App\Cache\CacheKeys;
use App\DTOs\AlbumDTO;
use Illuminate\Http\Request;
use App\Services\Neo4jService;
use Illuminate\Support\Facades\Cache;

class AlbumController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function index()
    {
        $raw = Cache::remember(CacheKeys::ALBUMS_INDEX, CacheKeys::TTL_LONG, function () {
            $client = $this->neo4j->client();
            $result = $client->run(
                'MATCH (a:Album {isActive: true})
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

        return view('albums.index', compact('albums'));
    }

    public function show($id)
    {
        $data = Cache::remember(CacheKeys::albumDetail((int) $id), CacheKeys::TTL_SHORT, function () use ($id) {
            $client = $this->neo4j->client();

            $albumResult = $client->run(
                'MATCH (a:Album {id: $id})
                 RETURN a',
                ['id' => (int) $id]
            );

            $albumRecord = $albumResult->first();
            if (!$albumRecord) {
                return null;
            }

            $album = $albumRecord->get('a')->getProperties()->toArray();

            // All characters reachable through this album's included media
            $charResult = $client->run(
                'MATCH (a:Album {id: $id})-[:INCLUDES_MEDIA]->(m:Media)-[r:HAS_CHARACTER]->(c:Character)
                 OPTIONAL MATCH (f:Franchise)-[:HAS_ENTRY]->(m)
                 RETURN c.id AS charId,
                        c.name AS charName,
                        c.image AS charImage,
                        m.id AS mediaId,
                        m.title AS mediaTitle,
                        m.coverImage AS mediaCover,
                        f.name AS franchise,
                        r.role AS role
                 ORDER BY f.name ASC, m.title ASC, c.name ASC',
                ['id' => (int) $id]
            );

            $mediaResult = $client->run(
                'MATCH (a:Album {id: $id})-[:INCLUDES_MEDIA]->(m:Media)
                 OPTIONAL MATCH (f:Franchise)-[:HAS_ENTRY]->(m)
                 RETURN m.id AS id, m.title AS title, m.coverImage AS coverImage, f.name AS franchise
                 ORDER BY f.name ASC, m.title ASC',
                ['id' => (int) $id]
            );

            $characters = [];
            foreach ($charResult as $rec) {
                $characters[] = [
                    'id'          => (int) $rec->get('charId'),
                    'name'        => $rec->get('charName'),
                    'image'       => $rec->get('charImage'),
                    'mediaId'     => (int) $rec->get('mediaId'),
                    'mediaTitle'  => $rec->get('mediaTitle'),
                    'mediaCover'  => $rec->get('mediaCover'),
                    'franchise'   => $rec->get('franchise'),
                    'role'        => $rec->get('role'),
                ];
            }

            $media = [];
            foreach ($mediaResult as $rec) {
                $media[] = [
                    'id'         => (int) $rec->get('id'),
                    'title'      => $rec->get('title'),
                    'coverImage' => $rec->get('coverImage'),
                    'franchise'  => $rec->get('franchise'),
                ];
            }

            return compact('album', 'characters', 'media');
        });

        if (!$data) {
            abort(404);
        }

        return view('albums.show', $data);
    }
}
