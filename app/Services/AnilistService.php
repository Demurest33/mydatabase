<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class AnilistService
{
    protected string $url = 'https://graphql.anilist.co';

    public function query(string $query, array $variables = []): array
    {
        $token = config('services.anilist.token');
        $http = Http::acceptJson();
        
        if ($token) {
            $http->withToken($token);
        }

        $response = $http->post($this->url, [
            'query' => $query,
            'variables' => $variables,
        ]);

        if ($response->failed()) {
            throw new Exception('Error de la API de AniList: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Hace el recorrido recursivo para encontrar toda la franquicia.
     */
    public function getFullFranchise(string $search): array
    {
        // 1. Búsqueda inicial
        $initialQuery = '
        query ($search: String) {
            Media(search: $search, type: ANIME) {
                id
                title { romaji native }
                description
                format type status source averageScore season seasonYear
                genres
                studios(isMain: true) { nodes { id name } }
                characters(page: 1, perPage: 30, sort: [ROLE, ID]) {
                    edges {
                        role
                        node { id name { full } image { large } }
                    }
                }
                coverImage { large extraLarge }
                bannerImage
                startDate { year month day }
                relations {
                    edges {
                        relationType
                        node { id type format title { romaji } }
                    }
                }
            }
        }
        ';

        $response = $this->query($initialQuery, ['search' => $search]);
        $initialMedia = $response['data']['Media'] ?? null;

        if (!$initialMedia) {
            return [];
        }

        $graph = [];
        $rootId = $initialMedia['id'];
        $graph[$rootId] = $initialMedia;

        // Extraer los ids directamente relacionados
        $unvisitedIds = $this->extractNewIds($initialMedia, $graph);

        // 2. Consulta en lote (Batch) por IDs
        $batchQuery = '
        query ($ids: [Int]) {
            Page(page: 1, perPage: 50) {
                media(id_in: $ids) {
                    id
                    title { romaji native }
                    description
                    format type status source averageScore season seasonYear
                    genres
                    studios(isMain: true) { nodes { id name } }
                    characters(page: 1, perPage: 30, sort: [ROLE, ID]) {
                        edges {
                            role
                            node { id name { full } image { large } }
                        }
                    }
                    coverImage { large extraLarge }
                    bannerImage
                    startDate { year month day }
                    relations {
                        edges {
                            relationType
                            node { id type format title { romaji } }
                        }
                    }
                }
            }
        }
        ';

        $depth = 0;
        $maxDepth = 4; // Protección contra bucles infinitos en grafos grandes

        // 3. Algoritmo de BFS para recorrer todas las relaciones de la serie
        while (!empty($unvisitedIds) && $depth < $maxDepth) {
            $batchResponse = $this->query($batchQuery, ['ids' => array_values($unvisitedIds)]);
            $newMedias = $batchResponse['data']['Page']['media'] ?? [];

            foreach ($newMedias as $m) {
                $graph[$m['id']] = $m;
            }

            // Volver a evaluar qué IDs nos faltan
            $unvisitedIds = [];
            foreach ($graph as $m) {
                $new = $this->extractNewIds($m, $graph);
                foreach ($new as $id) {
                    $unvisitedIds[$id] = $id;
                }
            }

            $depth++;
        }

        // 4. Procesar el grafo y separarlo en la línea del tiempo
        return $this->buildTimeline($graph, $rootId);
    }

    private function extractNewIds(array $media, array $graph): array
    {
        $ids = [];
        if (!empty($media['relations']['edges'])) {
            foreach ($media['relations']['edges'] as $edge) {
                $nodeId = $edge['node']['id'];
                if (!isset($graph[$nodeId])) {
                     $type = $edge['relationType'];
                     // Tipos de relaciones seguras para travesar la franquicia
                     $validTypes = ['ADAPTATION', 'PREQUEL', 'SEQUEL', 'PARENT', 'SIDE_STORY', 'SPIN_OFF', 'ALTERNATIVE', 'SOURCE', 'COMPILATION', 'SUMMARY'];
                     if (in_array($type, $validTypes)) {
                         $ids[$nodeId] = $nodeId;
                     }
                }
            }
        }
        return $ids;
    }

    private function buildTimeline(array $graph, int $rootId): array
    {
        $timeline = [];
        $sources = [];
        $others = [];

        // Inicializar etiquetas (tags)
        foreach ($graph as $id => $media) {
            $graph[$id]['tag'] = 'main'; // Por defecto es canon/main
            if ($media['type'] === 'MANGA' || $media['type'] === 'NOVEL') {
                $graph[$id]['tag'] = 'source';
            }
        }

        // Marcar spinoffs iterando sobre cómo se relacionan los animes entre sí
        foreach ($graph as $media) {
            if (!isset($media['relations']['edges'])) continue;
            
            foreach ($media['relations']['edges'] as $edge) {
                $rel = $edge['relationType'];
                $targetId = $edge['node']['id'];
                
                if (isset($graph[$targetId])) {
                    // Evitar degradar a 'other' a la fuente
                    if ($graph[$targetId]['tag'] === 'source') {
                        continue;
                    }

                    // Relaciones que degradan explícitamente el elemento a "otros/spin-offs"
                    // NOTA: Quitamos 'ALTERNATIVE' ya que pueden ser canon en su propio universo (ej FMA vs FMAB)
                    $demotingRelations = ['SPIN_OFF', 'PARODY', 'SUMMARY', 'SIDE_STORY', 'CHARACTER', 'COMPILATION'];
                    
                    if (in_array($rel, $demotingRelations)) {
                        
                        // Protección: Si el formato es 'TV' o 'MOVIE', y es un Spinoff/SideStory suele querer verse en el Watch Order (ej: Boruto, Railgun)
                        $targetFormat = $graph[$targetId]['format'] ?? '';
                        if (in_array($targetFormat, ['TV', 'MOVIE']) && in_array($rel, ['SPIN_OFF', 'SIDE_STORY'])) {
                            continue;
                        }

                        if ($graph[$targetId]['tag'] === 'main') {
                            $graph[$targetId]['tag'] = 'other';
                        }
                    }
                }
            }
        }
        
        // Excepción: El anime que buscó el usuario se fuerza como parte de la línea principal 
        if (isset($graph[$rootId]) && $graph[$rootId]['type'] === 'ANIME') {
            $graph[$rootId]['tag'] = 'main';
        }

        // Distribuir según el tag asignado
        foreach ($graph as $media) {
            if ($media['tag'] === 'source') {
                $sources[] = $media;
            } elseif ($media['tag'] === 'other') {
                $others[] = $media;
            } else {
                $timeline[] = $media;
            }
        }
        
        // Ordenar absolutamente por fecha cronológica de emisión/publicación
        usort($timeline, fn($a, $b) => $this->compareDates($a['startDate'], $b['startDate']));
        usort($others, fn($a, $b) => $this->compareDates($a['startDate'], $b['startDate']));
        usort($sources, fn($a, $b) => $this->compareDates($a['startDate'], $b['startDate']));
        
        return [
            'timeline' => $timeline,
            'source'   => $sources,
            'others'   => $others,
            'root'     => $graph[$rootId] ?? null
        ];
    }

    private function compareDates($d1, $d2) {
        $y1 = $d1['year'] ?? 9999;
        $y2 = $d2['year'] ?? 9999;
        if ($y1 !== $y2) return $y1 <=> $y2;
        
        $m1 = $d1['month'] ?? 12;
        $m2 = $d2['month'] ?? 12;
        if ($m1 !== $m2) return $m1 <=> $m2;
        
        $d1_day = $d1['day'] ?? 31;
        $d2_day = $d2['day'] ?? 31;
        return $d1_day <=> $d2_day;
    }

    public function getGraphData(string $search): array
    {
        $franchise = $this->getFullFranchise($search);

        $items = array_merge(
            $franchise['timeline'] ?? [],
            $franchise['source'] ?? [],
            $franchise['others'] ?? []
        );

        $media = [];
        $characters = [];
        $studios = [];
        $genres = [];
        $mediaRelations = [];
        $mediaCharacters = [];
        $mediaStudios = [];
        $mediaGenres = [];

        foreach ($items as $item) {

            $media[$item['id']] = [
                'id' => (int)$item['id'],
                'tag' => $item['tag'] ?? 'main',
                'title' => $item['title']['romaji'] ?? null,
                'native' => $item['title']['native'] ?? null,
                'type' => $item['type'] ?? null,
                'format' => $item['format'] ?? null,
                'status' => $item['status'] ?? null,
                'description' => $item['description'] ?? null,
                'season' => $item['season'] ?? null,
                'year' => $item['seasonYear'] ?? null,
                'score' => $item['averageScore'] ?? null,
                'coverImage' => $item['coverImage']['large'] ?? null,
                'bannerImage' => $item['bannerImage'] ?? null,
                'start_year' => $item['startDate']['year'] ?? null,
                'start_month' => $item['startDate']['month'] ?? null,
                'start_day' => $item['startDate']['day'] ?? null,
            ];

            foreach ($item['genres'] ?? [] as $genre) {
                $genres[$genre] = ['name' => $genre];

                $mediaGenres[] = [
                    'media_id' => $item['id'],
                    'genre' => $genre
                ];
            }

            foreach ($item['studios']['nodes'] ?? [] as $studio) {
                $studios[$studio['id']] = [
                    'id' => (int)$studio['id'],
                    'name' => $studio['name']
                ];

                $mediaStudios[] = [
                    'media_id' => $item['id'],
                    'studio_id' => $studio['id']
                ];
            }

            foreach ($item['characters']['edges'] ?? [] as $edge) {
                $char = $edge['node'];

                $characters[$char['id']] = [
                    'id' => (int)$char['id'],
                    'name' => $char['name']['full'],
                    'image' => $char['image']['large'] ?? null
                ];

                $mediaCharacters[] = [
                    'media_id' => $item['id'],
                    'character_id' => $char['id'],
                    'role' => $edge['role']
                ];
            }

            foreach ($item['relations']['edges'] ?? [] as $edge) {
                $node = $edge['node'];

                $mediaRelations[] = [
                    'from' => $item['id'],
                    'to' => $node['id'],
                    'type' => $edge['relationType']
                ];
            }
        }

        return [
            'media' => array_values($media),
            'characters' => array_values($characters),
            'studios' => array_values($studios),
            'genres' => array_values($genres),
            'media_relations' => $mediaRelations,
            'media_characters' => $mediaCharacters,
            'media_studios' => $mediaStudios,
            'media_genres' => $mediaGenres,
        ];
    }
}