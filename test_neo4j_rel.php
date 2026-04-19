<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$client = app(App\Services\Neo4jService::class)->client();
$result = $client->run('MATCH ()-[r]->() RETURN r LIMIT 1');
$rel = $result->first()->get('r');

echo "Class: " . get_class($rel) . "\n";
echo "Methods: " . implode(', ', get_class_methods($rel)) . "\n";
