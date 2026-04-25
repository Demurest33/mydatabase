<?php

namespace App\Http\Controllers;

use App\DTOs\AssetDTO;
use App\DTOs\MediaDTO;
use App\Services\Neo4jService;
use Illuminate\Http\Request;
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
            $client = $this->neo4j->client();

            $result = $client->run('
                MATCH (m:Media {id: $id})
                OPTIONAL MATCH (m)-[:HAS_ASSET]->(a:Asset)
                OPTIONAL MATCH (m)-[:HAS_GENRE]->(g:Genre)
                RETURN m,
                       collect(DISTINCT a) as assets,
                       collect(DISTINCT g.name) as genres
            ', ['id' => (int) $id]);

            if ($result->isEmpty()) {
                abort(404, 'Media item not found in graph database.');
            }

            $record = $result->first();
            $mNode  = $record->get('m');

            if ($mNode === null) {
                abort(404, 'Media item not found.');
            }

            $media = MediaDTO::from($mNode->getProperties()->toArray());

            $genres = array_values(array_filter($record->get('genres')->toArray()));

            $assets = [];
            foreach ($record->get('assets')->toArray() as $aNode) {
                if ($aNode === null) continue;

                $a = $aNode->getProperties()->toArray();

                // Normalise createdAt to a string before passing to DTO
                if (isset($a['createdAt']) && is_object($a['createdAt'])) {
                    $a['createdAtStr'] = method_exists($a['createdAt'], 'toDateTime')
                        ? $a['createdAt']->toDateTime()->format('c')
                        : (string) $a['createdAt'];
                } else {
                    $a['createdAtStr'] = $a['createdAt'] ?? now()->format('c');
                }

                $assets[] = AssetDTO::from($a);
            }

            usort($assets, fn(AssetDTO $a, AssetDTO $b) =>
                strnatcmp($a->title ?? (string) $a->id, $b->title ?? (string) $b->id)
            );

            return view('media.show', compact('media', 'genres', 'assets'));

        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
    }
}
