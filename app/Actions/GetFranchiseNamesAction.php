<?php

namespace App\Actions;

use App\Cache\CacheKeys;
use App\Services\Neo4jService;
use Illuminate\Support\Facades\Cache;
use Exception;

class GetFranchiseNamesAction
{
    private Neo4jService $neo4jService;

    public function __construct(Neo4jService $neo4jService)
    {
        $this->neo4jService = $neo4jService;
    }

    public function execute(): array
    {
        $cached = Cache::get(CacheKeys::FRANCHISE_NAMES);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $client = $this->neo4jService->client();
            $franchises = [];
            foreach ($client->run('MATCH (f:Franchise) RETURN f.name as name ORDER BY f.name ASC') as $record) {
                $franchises[] = $record->get('name');
            }
            Cache::put(CacheKeys::FRANCHISE_NAMES, $franchises, CacheKeys::TTL_LONG);
            return $franchises;
        } catch (Exception) {
            return [];
        }
    }
}
