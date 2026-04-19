<?php

namespace App\Services;

use Laudis\Neo4j\ClientBuilder;

class Neo4jService
{
    public function client()
    {
        return ClientBuilder::create()  
            ->withDriver('bolt', config('services.neo4j.uri'))
            ->build();
    }
}