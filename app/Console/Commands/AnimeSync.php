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
            $data = $anilist->getGraphData($search);
            app(\App\Actions\SaveAnilistGraphAction::class)->execute($search, $data);

            $this->info('Sincronización completada correctamente');

        } catch (\Throwable $e) {

            $this->error('Error: ' . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }


}