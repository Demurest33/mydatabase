<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AnilistService;
use App\Services\Neo4jService;

class AnimeSync extends Command
{
    protected $signature = 'anime:sync {search}';
    protected $description = 'Sincroniza una franquicia desde AniList hacia Neo4j';

    public function handle(
        AnilistService $anilist,
        Neo4jService $neo4jService
    ) {
        $search = $this->argument('search');

        $this->info("Buscando franquicia: {$search}");

        try {
            $franchiseName = $search;
            $data = $anilist->getGraphData($search);

            $neo = $neo4jService->client();

            $neo->run('
                MERGE (f:Franchise {name: $name})
            ', [
                'name' => $franchiseName
            ]);

            // =====================================
            // MEDIA
            // =====================================
            $neo->run('
                UNWIND $rows AS row
                MERGE (m:Media {id: row.id})
                SET m += row
            ', [
                'rows' => $data['media']
            ]);

            $neo->run('
                UNWIND $rows AS row
                MATCH (f:Franchise {name: $name})
                MATCH (m:Media {id: row.id})
                MERGE (f)-[:HAS_ENTRY]->(m)
            ', [
                'name' => $franchiseName,
                'rows' => $data['media']
            ]);

            $this->info('Media sincronizados');

            // =====================================
            // CHARACTERS
            // =====================================
            $neo->run('
                UNWIND $rows AS row
                MERGE (c:Character {id: row.id})
                SET c += row
            ', [
                'rows' => $data['characters']
            ]);

            $this->info('Characters sincronizados');

            // =====================================
            // STUDIOS
            // =====================================
            $neo->run('
                UNWIND $rows AS row
                MERGE (s:Studio {id: row.id})
                SET s += row
            ', [
                'rows' => $data['studios']
            ]);

            $this->info('Studios sincronizados');

            // =====================================
            // GENRES
            // =====================================
            $neo->run('
                UNWIND $rows AS row
                MERGE (g:Genre {name: row.name})
            ', [
                'rows' => $data['genres']
            ]);

            $this->info('Genres sincronizados');

            // =====================================
            // MEDIA -> CHARACTER
            // =====================================
            $neo->run('
                UNWIND $rows AS row
                MATCH (m:Media {id: row.media_id})
                MATCH (c:Character {id: row.character_id})
                MERGE (m)-[r:HAS_CHARACTER]->(c)
                SET r.role = row.role
            ', [
                'rows' => $data['media_characters']
            ]);

            $this->info('Relaciones Media->Character listas');

            // =====================================
            // MEDIA -> STUDIO
            // =====================================
            $neo->run('
                UNWIND $rows AS row
                MATCH (m:Media {id: row.media_id})
                MATCH (s:Studio {id: row.studio_id})
                MERGE (m)-[:PRODUCED_BY]->(s)
            ', [
                'rows' => $data['media_studios']
            ]);

            $this->info('Relaciones Media->Studio listas');

            // =====================================
            // MEDIA -> GENRE
            // =====================================
            $neo->run('
                UNWIND $rows AS row
                MATCH (m:Media {id: row.media_id})
                MATCH (g:Genre {name: row.genre})
                MERGE (m)-[:HAS_GENRE]->(g)
            ', [
                'rows' => $data['media_genres']
            ]);

            $this->info('Relaciones Media->Genre listas');

            // =====================================
            // MEDIA -> MEDIA
            // =====================================
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

            $this->info('Relaciones Media->Media listas');

            $this->info('Sincronización completada correctamente');

        } catch (\Throwable $e) {

            $this->error('Error: ' . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}