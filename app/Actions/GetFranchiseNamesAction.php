<?php

namespace App\Actions;

use App\Services\Neo4jService;

class GetFranchiseNamesAction
{
    private Neo4jService $neo4jService;

    public function __construct(Neo4jService $neo4jService)
    {
        $this->neo4jService = $neo4jService;
    }

    /**
     * Devuelve un arreglo simple con los nombres de todas las franquicias ordenadas alfabéticamente.
     */
    public function execute(): array
    {
        $client = $this->neo4jService->client();
        $franchises = [];
        
        $res = $client->run('MATCH (f:Franchise) RETURN f.name as name ORDER BY f.name ASC');
        foreach ($res as $record) {
            $franchises[] = $record->get('name');
        }

        return $franchises;
    }
}
