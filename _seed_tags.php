<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$client = app('App\Services\Neo4jService')->client();

// ── Tag definitions ───────────────────────────────────────────────────────────
// [ name, type, category ]

const TAGS = [
    // ── Hair Color ────────────────────────────────────────────────────────────
    ['Blonde',      'character', 'Hair Color'],
    ['Brown Hair',  'character', 'Hair Color'],
    ['Black Hair',  'character', 'Hair Color'],
    ['Red Hair',    'character', 'Hair Color'],
    ['White Hair',  'character', 'Hair Color'],
    ['Silver Hair', 'character', 'Hair Color'],
    ['Gray Hair',   'character', 'Hair Color'],
    ['Pink Hair',   'character', 'Hair Color'],
    ['Blue Hair',   'character', 'Hair Color'],
    ['Green Hair',  'character', 'Hair Color'],
    ['Purple Hair', 'character', 'Hair Color'],
    ['Orange Hair', 'character', 'Hair Color'],
    ['Teal Hair',   'character', 'Hair Color'],
    ['Multicolor Hair', 'character', 'Hair Color'],
    ['Bald',        'character', 'Hair Color'],

    // ── Hair Length ───────────────────────────────────────────────────────────
    ['Short Hair',     'character', 'Hair Length'],
    ['Medium Hair',    'character', 'Hair Length'],
    ['Long Hair',      'character', 'Hair Length'],
    ['Very Long Hair', 'character', 'Hair Length'],

    // ── Eye Color ─────────────────────────────────────────────────────────────
    ['Blue Eyes',      'character', 'Eye Color'],
    ['Green Eyes',     'character', 'Eye Color'],
    ['Brown Eyes',     'character', 'Eye Color'],
    ['Black Eyes',     'character', 'Eye Color'],
    ['Red Eyes',       'character', 'Eye Color'],
    ['Purple Eyes',    'character', 'Eye Color'],
    ['Yellow Eyes',    'character', 'Eye Color'],
    ['Gray Eyes',      'character', 'Eye Color'],
    ['Pink Eyes',      'character', 'Eye Color'],
    ['Heterochromia',  'character', 'Eye Color'],

    // ── Skin Tone ─────────────────────────────────────────────────────────────
    ['Light Skin', 'character', 'Skin Tone'],
    ['Fair Skin',  'character', 'Skin Tone'],
    ['Tan Skin',   'character', 'Skin Tone'],
    ['Brown Skin', 'character', 'Skin Tone'],
    ['Dark Skin',  'character', 'Skin Tone'],

    // ── Gender ────────────────────────────────────────────────────────────────
    ['Male',        'character', 'Gender'],
    ['Female',      'character', 'Gender'],
    ['Non-binary',  'character', 'Gender'],
    ['Androgynous', 'character', 'Gender'],

    // ── Body Type ─────────────────────────────────────────────────────────────
    ['Slender',        'character', 'Body Type'],
    ['Muscular',       'character', 'Body Type'],
    ['Chubby',         'character', 'Body Type'],
    ['Small Breasts',  'character', 'Body Type'],
    ['Large Breasts',  'character', 'Body Type'],
    ['Huge Breasts',   'character', 'Body Type'],

    // ── Features ──────────────────────────────────────────────────────────────
    ['Nekomimi',  'character', 'Features'],
    ['Fox Ears',  'character', 'Features'],
    ['Elf Ears',  'character', 'Features'],
    ['Horns',     'character', 'Features'],
    ['Tail',      'character', 'Features'],
    ['Wings',     'character', 'Features'],
    ['Fangs',     'character', 'Features'],
    ['Scales',    'character', 'Features'],

    // ── Accessories ───────────────────────────────────────────────────────────
    ['Glasses',      'character', 'Accessories'],
    ['Sunglasses',   'character', 'Accessories'],
    ['Eyepatch',     'character', 'Accessories'],
    ['Mask',         'character', 'Accessories'],
    ['Hat',          'character', 'Accessories'],
    ['Hair Ribbon',  'character', 'Accessories'],
    ['Ahoge',        'character', 'Accessories'],
];

// ─────────────────────────────────────────────────────────────────────────────

use Illuminate\Support\Str;

$created = 0;
$skipped = 0;

foreach (TAGS as [$name, $type, $category]) {
    $check = $client->run(
        'MATCH (t:Tag {name: $name, type: $type}) RETURN t.id LIMIT 1',
        ['name' => $name, 'type' => $type]
    );

    if ($check->count() > 0) {
        echo "SKIP  [{$category}] {$name}\n";
        $skipped++;
        continue;
    }

    $client->run(
        'CREATE (t:Tag {id: $id, name: $name, slug: $slug, type: $type, category: $category})',
        [
            'id'       => rand(10000000, 99999999),
            'name'     => $name,
            'slug'     => Str::slug($name),
            'type'     => $type,
            'category' => $category,
        ]
    );

    echo "OK    [{$category}] {$name}\n";
    $created++;
}

echo "\nListo: {$created} creados, {$skipped} omitidos.\n";

foreach (['tags.character', 'characters.grouped', 'admin.characters.grouped'] as $key) {
    \Illuminate\Support\Facades\Cache::forget($key);
}
echo "Caché limpiada.\n";
