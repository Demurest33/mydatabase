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
            app(\App\Actions\SaveAnilistGraphAction::class)->execute($this->search, $data);

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



    public function failed(\Throwable $exception): void
    {
        if ($this->batchId !== 'none') {
            \Illuminate\Support\Facades\Cache::increment("batch_{$this->batchId}_error");
        }
    }
}
