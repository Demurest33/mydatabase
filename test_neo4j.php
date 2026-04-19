<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$client = app(App\Services\Neo4jService::class)->client();
$result = $client->run('MATCH (f:Franchise) RETURN f LIMIT 1');
$node = $result->first()->get('f');

echo "Class: " . get_class($node) . "\n";
echo "Methods: " . implode(', ', get_class_methods($node)) . "\n";
