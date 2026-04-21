<?php

namespace App\Actions;

use App\Services\Neo4jService;

class SaveAnilistGraphAction
{
    private Neo4jService $neo4jService;

    public function __construct(Neo4jService $neo4jService)
    {
        $this->neo4jService = $neo4jService;
    }

    public function execute(string $search, array $data): void
    {
        if (empty($data['media'])) {
            return; // No data to save
        }

        $mainMedia = $this->detectFranchiseMainMedia($data['media']);
        $franchiseName = $mainMedia['title'] ?? $search;
        $franchiseImage = $mainMedia['coverImage'] ?? null;
        $franchiseBanner = $mainMedia['bannerImage'] ?? null;
        $neo = $this->neo4jService->client();

        $neo->run('
            MERGE (f:Franchise {name: $name})
            SET f.image = $image, f.bannerImage = $banner
        ', [
            'name' => $franchiseName,
            'image' => $franchiseImage,
            'banner' => $franchiseBanner
        ]);

        $neo->run('
            UNWIND $rows AS row
            MERGE (m:Media {id: row.id})
            SET m += row
        ', ['rows' => $data['media']]);

        $neo->run('
            UNWIND $rows AS row
            MATCH (f:Franchise {name: $name})
            MATCH (m:Media {id: row.id})
            MERGE (f)-[:HAS_ENTRY]->(m)
        ', [
            'name' => $franchiseName,
            'rows' => $data['media']
        ]);

        $neo->run('
            UNWIND $rows AS row
            MERGE (c:Character {id: row.id})
            SET c += row
        ', ['rows' => $data['characters']]);

        $neo->run('
            UNWIND $rows AS row
            MERGE (s:Studio {id: row.id})
            SET s += row
        ', ['rows' => $data['studios']]);

        $neo->run('
            UNWIND $rows AS row
            MERGE (g:Genre {name: row.name})
        ', ['rows' => $data['genres']]);

        $neo->run('
            UNWIND $rows AS row
            MATCH (m:Media {id: row.media_id})
            MATCH (c:Character {id: row.character_id})
            MERGE (m)-[r:HAS_CHARACTER]->(c)
            SET r.role = row.role
        ', ['rows' => $data['media_characters']]);

        $neo->run('
            UNWIND $rows AS row
            MATCH (m:Media {id: row.media_id})
            MATCH (s:Studio {id: row.studio_id})
            MERGE (m)-[:PRODUCED_BY]->(s)
        ', ['rows' => $data['media_studios']]);

        $neo->run('
            UNWIND $rows AS row
            MATCH (m:Media {id: row.media_id})
            MATCH (g:Genre {name: row.genre})
            MERGE (m)-[:HAS_GENRE]->(g)
        ', ['rows' => $data['media_genres']]);

        foreach ($data['media_relations'] as $rel) {
            $type = strtoupper($rel['type']);
            $query = "
                MATCH (a:Media {id: \$from})
                MATCH (b:Media {id: \$to})
                MERGE (a)-[:{$type}]->(b)
            ";

            $neo->run($query, [
                'from' => $rel['from'],
                'to'   => $rel['to'],
            ]);
        }
    }

    private function detectFranchiseMainMedia(array $media): ?array
    {
        if (empty($media)) {
            return null;
        }

        usort($media, function ($a, $b) {
            $yearA = $a['year'] ?? ($a['start_year'] ?? 9999);
            $yearB = $b['year'] ?? ($b['start_year'] ?? 9999);

            if ($yearA !== $yearB) {
                return $yearA <=> $yearB;
            }

            $priority = [
                'TV' => 1,
                'ONA' => 2,
                'MOVIE' => 3,
                'OVA' => 4,
                'SPECIAL' => 5,
            ];

            $fa = $priority[$a['format'] ?? 'ZZZ'] ?? 99;
            $fb = $priority[$b['format'] ?? 'ZZZ'] ?? 99;

            return $fa <=> $fb;
        });

        return $media[0];
    }
}
