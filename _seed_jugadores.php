<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$client = app('App\Services\Neo4jService')->client();

// ── Configuración ─────────────────────────────────────────────
const MEDIA_TITLE = 'México 2026';

const PLAYERS = [
    ['Javier Aguirre', 'https://ca-times.brightspotcdn.com/dims4/default/37694fc/2147483647/strip/true/crop/6490x4327+0+0/resize/1200x800!/quality/75/?url=https%3A%2F%2Fcalifornia-times-brightspot.s3.amazonaws.com%2Ff6%2F0f%2Fb48c1c97ea7611fadd85a093091e%2F930a1790a6474f73ac0239b39699d966'],
    ['Luis Malagón', 'https://a.espncdn.com/photo/2023/0611/r1185112_1296x729_16-9.jpg'],
    ['Raúl Rangel', 'https://assets.goal.com/images/v3/getty-2234594884/crop/MM5DINZZHE5DENRZHE5G433XMU5DAORSGUYQ====/GettyImages-2234594884.jpg?auto=webp&format=pjpg&width=3840&quality=60'],
    ['Carlos Acevedo', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ3KVlCzq5ZBqYez2p3p-tTQ8umHRJDSAFLxQ&s'],
    ['Guillermo Ochoa', 'https://upload.wikimedia.org/wikipedia/commons/b/b3/Ger-Mex_%285_cropped%29.jpg'],
    ['Kevin Álvarez', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT4M3uajbGc2fbB1KyL5v7MuIv_MFhR7qW7DQ&s'],
    ['Jesús Garza', 'https://cdn.amxinfra.com/clarosports/images/2026/02/jesus-garza-175720-1024x576.jpg'],
    ['César Montes', 'https://heraldodemexico.com.mx/u/fotografias/m/2022/12/26/f414x232-643322_657061_5614.jpg'],
    ['Víctor Guzmán', 'https://tmssl.akamaized.net//images/foto/galerie/victor-guzman-1692129157-114134.jpeg'],
    ['Johan Vásquez', 'https://upload.wikimedia.org/wikipedia/commons/1/1c/Johan_V%C3%A1squez_2.png'],
    ['Érik Lira', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSUMhCZeijEp7kjUhDhK5LE8W7IAsbyQDBGaw&s'],
    ['Luis Romo', 'https://www.somoschivas.com.mx/_image?href=https%3A%2F%2Fwww.somoschivas.com.mx%2Fimage%2Fsomoschivascommx%2Fluis-romo-volvio-a-tener-minutos-ante-bolivia-1769398200.webp&w=1280&h=720&q=80&f=webp'],
    ['Israel Reyes', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRqtFYMd-Q2skazgZZEv6ovuORcBISKloD6YA&s'],
    ['Ramón Juárez', 'https://upload.wikimedia.org/wikipedia/commons/8/8c/Ram%C3%B3n_Ju%C3%A1rez_3.png'],
    ['Diego Campillo', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTR_evexo19Gcpp6UHvcVb6O56SZhTkRRo6Rw&s'],
    ['Jesús Orozco', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTTMQzZ5nXLoEMzJMfnH3eI0n7qsBMTIEJEw&s'],
    ['Eduardo Águila', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQY010w0N0Z4K9PLJf_tNS1goryjMJ9fqSieQ&s'],
    ['Everardo López', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSKwKsoKT0D497Ad8FXvNtvNu8lMHHdteku-w&s'],
    ['Jesús Angulo', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSKwKsoKT0D497Ad8FXvNtvNu8lMHHdteku-w&s'],
    ['Mateo Chávez', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT0gHN96VSeW4f3Y6fPg0O68nVCRflwQjPjDA&s'],
    ['Jesús Gallardo', 'https://upload.wikimedia.org/wikipedia/commons/1/1e/Jes%C3%BAs_Gallardo.png'],
    ['Jorge Sánchez', 'https://www.tudn.com/_next/image?url=https%3A%2F%2Fst1.uvnimg.com%2F27%2Fba%2F2ede114f4b97b4ab8fb2e0326fa2%2F4ca590ec2ffd46ad91a10327a1888d7f&w=1280&q=75'],
    ['Richard Ledezma', 'https://media.bolavip.com/wp-content/uploads/sites/11/2026/02/26131906/Imago-1753757-e1772133640516-490x275.webp'],
    ['Bryan González', 'https://diario.mx/core/dmx/assets/images/2024/06/12/web-tri-G9vk6G2nB.jpg'],
    ['Edson Álvarez', 'https://upload.wikimedia.org/wikipedia/commons/9/91/Edson_%C3%81lvarez.png'],
    ['Carlos Rodríguez', 'https://laopcion.com.mx/__export/sites/laopcion/img/2022/11/19/Z3Ahul5LrtDgWXpT.jpg'],
    ['Erick Sánchez', 'https://a.espncdn.com/photo/2023/1018/r1239871_1296x729_16-9.jpg'],
    ['Marcel Ruíz', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQNmeEAQEtErkzFy2R4m3GdXSnKH7YNheepHg&s'],
    ['Fidel Ambríz', 'https://www.365scores.com/es/news/wp-content/uploads/2023/10/Fidel-Ambriz-Seleccion-Mexicana-Sub-21-2023.jpg'],
    ['Obed Vargas', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKVBLfJY__SaGX5v1rGMiUd8dfXxjWpyB6OA&s'],
    ['Denzell García', 'https://oem.com.mx/elsoldesinaloa/img/28555197/1771537641/BASE_LANDSCAPE/1200/image.webp'],
    ['Iker Fimbres', 'https://storage.rayados.com/noticias/g/21024_20251007_3538.jpg'],
    ['Alexis Gutiérrez', 'https://i.imgur.com/TZLFJAz.jpeg'],
    ['Orbelín Pineda', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7Chz4KEZp8_NLwqagrtBE-Ts_tDvseDyyZA&s'],
    ['Álvaro Fidalgo', 'https://blob.soyreferee.com/images/2025/10/09/fidalgo-ede15578-focus-0-0-810-550.webp'],
    ['Brian Gutiérrez', 'https://sportal365images.com/process/smp-images-production/record.mx/23012026/8f4db277-90ad-4047-b525-bae17ab7596a.webp?operations=autocrop(860:484)'],
    ['Efraín Álvarez', 'https://d6isf1yxni2j5.cloudfront.net/large_efrain_alvarez_le_da_el_triunfo_a_la_seleccion_mexicana_31b4ecaa3d.jpg'],
    ['Kevin Castañeda', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQpiiDnATUv-_qSgtw_Q8rF564_m7M9EQuN-g&s'],
    ['Roberto Alvarado', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSYiAtanlSIsIXThvQm3aBBcwFm_F6B8tT2xA&s'],
    ['Diego Lainez', 'https://upload.wikimedia.org/wikipedia/commons/1/18/Diego_Lainez.png'],
    ['Alexis Vega', 'https://assets.goal.com/images/v3/blt355b47e5766eefd8/GettyImages-2162482353%20(1).jpg?auto=webp&format=pjpg&width=3840&quality=60'],
    ['Jorge Ruvalcaba', 'https://i.redd.it/r98znf6bmhde1.jpeg'],
    ['Hirving Lozano', 'https://images2.minutemediacdn.com/image/upload/c_crop,w_3937,h_2214,x_0,y_262/c_fill,w_1200,ar_4:3,f_auto,q_auto,g_auto/images/voltaxMediaLibrary/mmsport/theplayertribune/01ghxryg3amkpbq8fh5p.jpg'],
    ['Germán Berterame', 'https://static.foxdeportes.com/2025/08/29/cadenas_18_2_34_768.jpg'],
    ['Ángel Sepúlveda', 'https://assets.goal.com/images/v3/getty-2227178213/crop/MM5DKMBQGM5DEOBRGQ5G433XMU5DAORSGYYQ====/GettyImages-2227178213.jpg?auto=webp&format=pjpg&width=3840&quality=60'],
    ['Julián Quiñones', 'https://www.nmas.com.mx/_next/image/?url=https%3A%2F%2Fstatic-live.nmas.com.mx%2Fnmas-news%2Fstyles%2Fcorte_16_9%2Fcloud-storage%2F2025-10%2FJuli%25C3%25A1n%2520Qui%25C3%25B1ones%2520Anota%2520Hat-Trick%2520en%2520Arabia%253B%2520Supera%2520a%2520Cristiano%2520Ronaldo%2520en%2520la%2520Tabla%2520de%2520Goleo.jpg%3Fh%3D920929c4%26itok%3DnlVnrG1r&w=1920&q=75'],
    ['Armando González', 'https://upload.wikimedia.org/wikipedia/commons/b/bf/Armando_Gonz%C3%A1lez.png'],
];

// ─────────────────────────────────────────────────────────────

// Buscar el media
$mediaResult = $client->run(
    'MATCH (m:Media {title: $title}) RETURN m.id AS id LIMIT 1',
    ['title' => MEDIA_TITLE]
);

if ($mediaResult->count() === 0) {
    echo "ERROR: No se encontró el media \"" . MEDIA_TITLE . "\".\n";
    exit(1);
}

$mediaId = (int) $mediaResult->first()->get('id');
echo "Media: " . MEDIA_TITLE . " (id: {$mediaId})\n\n";

$created = 0;
$skipped = 0;

foreach (PLAYERS as [$name, $image]) {
    // Skip if character with same name already linked to this media
    $check = $client->run(
        'MATCH (m:Media {id: $mediaId})-[:HAS_CHARACTER]->(c:Character {name: $name}) RETURN c.id AS id LIMIT 1',
        ['mediaId' => $mediaId, 'name' => $name]
    );

    if ($check->count() > 0) {
        echo "SKIP  {$name} (ya existe en este media)\n";
        $skipped++;
        continue;
    }

    $id = rand(10000000, 99999999);
    $client->run(
        'MATCH (m:Media {id: $mediaId})
         CREATE (c:Character {id: $id, name: $name, image: $image, priority: 0})
         CREATE (m)-[:HAS_CHARACTER {role: "SUPPORTING"}]->(c)',
        ['mediaId' => $mediaId, 'id' => $id, 'name' => $name, 'image' => $image]
    );

    echo "OK    {$name}\n";
    $created++;
}

echo "\nListo: {$created} creados, {$skipped} omitidos.\n";

// Limpiar caché relevante
foreach (['characters.grouped', 'admin.characters.grouped', 'franchises.catalogue', 'admin.franchises.index'] as $key) {
    \Illuminate\Support\Facades\Cache::forget($key);
}
\Illuminate\Support\Facades\Cache::forget('media.detail.' . $mediaId);
echo "Caché limpiada.\n";
