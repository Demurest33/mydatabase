<?php

namespace App\Http\Controllers;

use App\Cache\CacheKeys;
use App\DTOs\AssetDTO;
use App\DTOs\MediaDTO;
use App\Services\Neo4jService;
use Illuminate\Support\Facades\Cache;
use Exception;

class MediaController extends Controller
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function show($id)
    {
        try {
            $raw = Cache::remember(CacheKeys::mediaDetail((int) $id), CacheKeys::TTL_SHORT, function () use ($id) {
                return $this->buildRaw((int) $id);
            });

            if ($raw === null) {
                abort(404, 'Media item not found.');
            }

            $media  = MediaDTO::from($raw['media']);
            $genres = $raw['genres'];
            $assets = array_map(fn($a) => AssetDTO::from($a), $raw['assets']);

            usort($assets, fn(AssetDTO $a, AssetDTO $b) =>
                strnatcmp($a->title ?? (string) $a->id, $b->title ?? (string) $b->id)
            );

            return view('media.show', compact('media', 'genres', 'assets'));

        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    private function buildRaw(int $id): ?array
    {
        $client = $this->neo4j->client();
        $result = $client->run('
            MATCH (m:Media {id: $id})
            OPTIONAL MATCH (m)-[:HAS_ASSET]->(a:Asset)
            OPTIONAL MATCH (m)-[:HAS_GENRE]->(g:Genre)
            RETURN m,
                   collect(DISTINCT a) as assets,
                   collect(DISTINCT g.name) as genres
        ', ['id' => $id]);

        if ($result->isEmpty() || $result->first()->get('m') === null) {
            return null;
        }

        $record = $result->first();
        $genres = array_values(array_filter($record->get('genres')->toArray()));

        $assets = [];
        foreach ($record->get('assets')->toArray() as $aNode) {
            if ($aNode === null) continue;
            $a = $aNode->getProperties()->toArray();
            if (isset($a['createdAt']) && is_object($a['createdAt'])) {
                $a['createdAt'] = method_exists($a['createdAt'], 'toDateTime')
                    ? $a['createdAt']->toDateTime()->format('c')
                    : (string) $a['createdAt'];
            }
            $assets[] = $a;
        }

        return [
            'media'  => $record->get('m')->getProperties()->toArray(),
            'genres' => $genres,
            'assets' => $assets,
        ];
    }
}
