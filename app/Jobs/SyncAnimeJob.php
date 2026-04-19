<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\AnilistService;
use App\Services\Neo4jService;
use Illuminate\Support\Facades\Log;

class SyncAnimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $search;
    public ?int $anilistId;
    public string $type;
    public string $batchId;
    public int $tries = 50;
    public int $backoff = 5;

    public function __construct(string $search, string $batchId = 'none', ?int $anilistId = null, string $type = 'ANIME')
    {
        $this->search = $search;
        $this->batchId = $batchId;
        $this->anilistId = $anilistId;
        $this->type = $type;
    }

    public function handle(AnilistService $anilist, Neo4jService $neo4jService): void
    {
        $idLog = $this->anilistId ? " (ID: {$this->anilistId}, Type: {$this->type})" : " (Type: {$this->type})";
        Log::info("Job Iniciado: Buscando franquicia: {$this->search}{$idLog}");

        try {
            $data = $anilist->getGraphData($this->search, $this->anilistId, $this->type);
            $franchiseName = $this->detectFranchiseName($data['media']);
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

            Log::info('Media sincronizados');

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

            Log::info('Characters sincronizados');

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

            Log::info('Studios sincronizados');

            // =====================================
            // GENRES
            // =====================================
            $neo->run('
                UNWIND $rows AS row
                MERGE (g:Genre {name: row.name})
            ', [
                'rows' => $data['genres']
            ]);

            Log::info('Genres sincronizados');

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

            Log::info('Relaciones Media->Character listas');

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

            Log::info('Relaciones Media->Studio listas');

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

            Log::info('Relaciones Media->Genre listas');

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

            Log::info("Sincronización completada correctamente para {$this->search}");
            if ($this->batchId !== 'none') {
                \Illuminate\Support\Facades\Cache::increment("batch_{$this->batchId}_done");
            }

        } catch (\App\Exceptions\RateLimitException $e) {
            $retry = $e->getRetryAfter();
            Log::warning("Rate limit hit in job for {$this->search}. Retrying in {$retry} seconds.");
            \Illuminate\Support\Facades\Cache::put('wyr_rate_limit_alert', now()->addSeconds($retry)->timestamp, now()->addSeconds($retry));
            $this->release($retry);
        } catch (\Throwable $e) {
            Log::error('Error en SyncAnimeJob ("' . $this->search . '"): ' . $e->getMessage());
            $this->fail($e);
        }
    }

    private function detectFranchiseName(array $media): string
    {
        if (empty($media)) {
            return 'Unknown Franchise (' . $this->search . ')';
        }

        usort($media, function ($a, $b) {

            $yearA = $a['seasonYear'] ?? 9999;
            $yearB = $b['seasonYear'] ?? 9999;

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

        return $media[0]['title'] ?? $this->search;
    }

    public function failed(\Throwable $exception): void
    {
        if ($this->batchId !== 'none') {
            \Illuminate\Support\Facades\Cache::increment("batch_{$this->batchId}_error");
        }
    }
}
