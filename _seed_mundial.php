<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$client = app('App\Services\Neo4jService')->client();

$teams = [
    ['Estados Unidos',  'https://a.espncdn.com/photo/2022/0331/r993396_1000x1000_1-1.png'],
    ['México',          'https://a.espncdn.com/photo/2022/0707/r1033816_500x500_1-1.png'],
    ['Canadá',          'https://a.espncdn.com/photo/2022/0327/r992022_1200x1200_1-1.png'],
    ['Japón',           'https://a.espncdn.com/photo/2022/0324/r990768_1000x1000_1-1.png'],
    ['Nueva Zelanda',   'https://tse2.mm.bing.net/th?id=OIP.UmXV8VSr3lxHcTYeUmseUwHaHa&w=474&h=474&c=7'],
    ['Argentina',       'https://a.espncdn.com/photo/2023/0417/r1160276_1000x1000_1-1.png'],
    ['Uzbeksitán',      'https://a.espncdn.com/photo/2025/1008/r1557460_1200x1200_1-1.png'],
    ['Corea del Sur',   'https://a.espncdn.com/photo/2022/0324/r990769_1042x1042_1-1.png'],
    ['Jordania',        'https://a.espncdn.com/photo/2025/0610/r1504932_1000x1000_1-1.png'],
    ['Australia',       'https://a.espncdn.com/photo/2022/0613/r1024770_1200x1200_1-1.png'],
    ['Brasil',          'https://a.espncdn.com/photo/2022/0716/r1036946_1000x1000_1-1.png'],
    ['Tunez',           'https://a.espncdn.com/photo/2022/0329/r992791_1200x1200_1-1.png'],
    ['Argelia',         'https://a.espncdn.com/photo/2025/1009/r1557930_2_350x350_1-1.png'],
    ['Ghana',           'https://a.espncdn.com/photo/2022/0628/r1030068_500x500_1-1.png'],
    ['Cabo Verde',      'https://a.espncdn.com/photo/2025/1013/r1559664_205x205_1-1.jpg'],
    ['Sudáfrica',       'https://a.espncdn.com/photo/2025/1014/r1560075_2_1000x1000_1-1.png'],
    ['Qatar',           'https://a.espncdn.com/photo/2021/1011/r921591_1200x1200_1-1.png'],
    ['Inglaterra',      'https://a.espncdn.com/photo/2023/1121/r1256225_1000x1000_1-1.png'],
    ['Arabia Saudita',  'https://a.espncdn.com/photo/2022/0725/r1040361_600x600_1-1.png'],
    ['Costa de Marfil', 'https://a.espncdn.com/photo/2025/1014/r1560165_1024x1024_1-1.png'],
    ['Senegal',         'https://a.espncdn.com/photo/2022/0701/r1031088_512x512_1-1.png'],
    ['Francia',         'https://a.espncdn.com/photo/2022/0708/r1033925_600x600_1-1.png'],
    ['Croacia',         'https://a.espncdn.com/photo/2022/0715/r1036647_1000x1000_1-1.png'],
    ['Portugal',        'https://a.espncdn.com/photo/2022/0329/r992770_1000x1000_1-1.png'],
    ['Noruega',         'https://a.espncdn.com/photo/2025/1116/r1576458_2_181x181_1-1.jpg'],
    ['Bélgica',         'https://a.espncdn.com/photo/2021/1114/r937295_1000x1000_1-1.png'],
    ['Suiza',           'https://a.espncdn.com/photo/2022/0718/r1037838_1000x1000_1-1.png'],
    ['España',          'https://a.espncdn.com/photo/2022/0705/r1032862_512x512_1-1.png'],
    ['Austria',         'https://a.espncdn.com/photo/2023/1016/r1239236_500x500_1-1.png'],
    ['Escocia',         'https://a.espncdn.com/photo/2023/1121/r1256224_1000x1000_1-1.png'],
    ['Curazao',         'https://a.espncdn.com/photo/2025/1119/r1577730_2_1000x1000_1-1.png'],
    ['Haití',           'https://a.espncdn.com/photo/2025/1119/r1577732_800x800_1-1.png'],
    ['Panamá',          'https://a.espncdn.com/photo/2025/1119/r1577731_1000x1000_1-1.png'],
    ['Suecia',          'https://a.espncdn.com/photo/2025/1120/r1578217_1000x1000_1-1.png'],
    ['Turquía',         'https://a.espncdn.com/photo/2023/1121/r1256229_1200x1200_1-1.png'],
    ['República Checa', 'https://a.espncdn.com/photo/2025/1120/r1578213_1000x1000_1-1.png'],
    ['RD Congo',        'https://a.espncdn.com/photo/2025/1121/r1578461_300x300_1-1.png'],
    ['Irak',            'https://a.espncdn.com/photo/2025/1121/r1578458_1200x1200_1-1.png'],
];

$created = 0;
$skipped = 0;

foreach ($teams as [$name, $image]) {
    $mediaTitle = $name . ' 2026';

    // Create franchise if it doesn't exist
    $client->run('MERGE (f:Franchise {name: $name})', ['name' => $name]);

    // Create media only if no media with this title already exists under the franchise
    $check = $client->run(
        'MATCH (f:Franchise {name: $franchise})-[:HAS_ENTRY]->(m:Media {title: $title}) RETURN m.id AS id LIMIT 1',
        ['franchise' => $name, 'title' => $mediaTitle]
    );

    if ($check->count() > 0) {
        echo "SKIP  {$mediaTitle} (already exists)\n";
        $skipped++;
        continue;
    }

    $id = rand(10000000, 99999999);
    $client->run(
        'MATCH (f:Franchise {name: $franchise})
         CREATE (m:Media {
             id:          $id,
             title:       $title,
             native:      "",
             format:      "ALBUM",
             status:      "RELEASING",
             description: "",
             coverImage:  $image,
             start_year:  2026
         })
         CREATE (f)-[:HAS_ENTRY]->(m)',
        [
            'franchise' => $name,
            'id'        => $id,
            'title'     => $mediaTitle,
            'image'     => $image,
        ]
    );

    echo "OK    {$mediaTitle}\n";
    $created++;
}

echo "\nListo: {$created} creados, {$skipped} omitidos.\n";

// Clear relevant caches
$keys = [
    'franchises.names',
    'franchises.catalogue',
    'admin.franchises.index',
    'admin.media.grouped',
    'characters.franchise_media',
    'admin.characters.franchise_media',
];
foreach ($keys as $key) {
    \Illuminate\Support\Facades\Cache::forget($key);
}
echo "Caché limpiada.\n";
