<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnilistService;
use App\Services\Neo4jService;
use App\Jobs\SyncAnimeJob;
use Illuminate\Support\Facades\Cache;

class WouldYouRatherController extends Controller
{
    public function index()
    {
        return view('would_you_rather.index');
    }

    public function fetch(Request $request, AnilistService $anilist, Neo4jService $neo4jService)
    {
        $name = $request->input('username');
        if (!$name) {
            return redirect()->back()->with('error', 'Por favor ingresa un usuario');
        }

        $query = '
            query ($name: String) {
                User(name: $name) {
                    id
                    name
                    avatar { large }
                    favourites {
                        anime { nodes { id title { romaji english native } coverImage { large } } }
                        manga { nodes { id title { romaji english native } coverImage { large } } }
                        characters { nodes { id name { full native } image { large } media(page: 1, perPage: 1) { nodes { id type title { romaji } } } } }
                    }
                }
            }
        ';

        try {
            $response = $anilist->query($query, ['name' => $name]);
            $user = $response['data']['User'] ?? null;

            if (!$user) {
                return redirect()->back()->with('error', 'Usuario no encontrado');
            }

            // Extract IDs
            $animeNodes = $user['favourites']['anime']['nodes'] ?? [];
            foreach ($animeNodes as &$node) { $node['type'] = 'ANIME'; }

            $mangaNodes = $user['favourites']['manga']['nodes'] ?? [];
            foreach ($mangaNodes as &$node) { $node['type'] = 'MANGA'; }

            $charNodes = $user['favourites']['characters']['nodes'] ?? [];

            $mediaItems = array_merge($animeNodes, $mangaNodes);

            // Filter what we have in Neo4j
            $neo = $neo4jService->client();
            
            // Check existing Media
            $mediaIds = array_column($mediaItems, 'id');
            $existingMedia = [];
            if (!empty($mediaIds)) {
                $res = $neo->run('MATCH (m:Media) WHERE m.id IN $ids RETURN m.id as id', ['ids' => $mediaIds]);
                foreach ($res as $row) {
                    $existingMedia[] = $row->get('id');
                }
            }
            
            // Check existing Characters
            $charIds = array_column($charNodes, 'id');
            $existingCharacters = [];
            if (!empty($charIds)) {
                $res = $neo->run('MATCH (c:Character) WHERE c.id IN $ids RETURN c.id as id', ['ids' => $charIds]);
                foreach ($res as $row) {
                    $existingCharacters[] = $row->get('id');
                }
            }

            $missingMedia = array_filter($mediaItems, fn($m) => !in_array($m['id'], $existingMedia));
            $missingCharacters = array_filter($charNodes, fn($c) => !in_array($c['id'], $existingCharacters));

            // Determine all unique media that need to be synced
            $syncTasks = [];
            foreach ($missingMedia as $media) {
                $syncTasks[$media['id']] = [
                    'id' => $media['id'],
                    'title' => $media['title']['romaji'] ?? $media['title']['english'] ?? $media['title']['native'],
                    'type' => $media['type']
                ];
            }

            // If a character is missing, we need to sync its parent franchise
            foreach ($missingCharacters as $char) {
                if (!empty($char['media']['nodes'])) {
                    $media = $char['media']['nodes'][0];
                    if (!isset($syncTasks[$media['id']])) {
                        $syncTasks[$media['id']] = [
                            'id' => $media['id'],
                            'title' => $media['title']['romaji'] ?? ($char['name']['full'] . "'s Series"),
                            'type' => $media['type']
                        ];
                    }
                }
            }

            $queuedJobs = [];
            $batchId = uniqid('wyr_');
            $queuedCount = count($syncTasks);
            
            if ($queuedCount > 0) {
                Cache::put("batch_{$batchId}_total", $queuedCount, now()->addHours(2));
                Cache::put("batch_{$batchId}_done", 0, now()->addHours(2));
                Cache::put("batch_{$batchId}_error", 0, now()->addHours(2));
            }

            foreach ($syncTasks as $task) {
                if ($task['title']) {
                    SyncAnimeJob::dispatch($task['title'], $batchId, $task['id'], $task['type']);
                    $queuedJobs[] = $task['title'];
                }
            }

            // Store favorites in session for the game
            session(['wyr_fav_media' => array_column($mediaItems, 'id')]);
            session(['wyr_fav_chars' => array_column($charNodes, 'id')]);

            // Return status
            return view('would_you_rather.status', [
                'user' => $user,
                'missingMedia' => $missingMedia,
                'missingCharacters' => $missingCharacters,
                'existingMediaCount' => count($existingMedia),
                'existingCharactersCount' => count($existingCharacters),
                'queuedJobs' => $queuedJobs,
                'batchId' => $batchId
            ]);

        } catch (\App\Exceptions\RateLimitException $e) {
            $seconds = $e->getRetryAfter();
            return redirect()->back()->with('error', "El límite de peticiones de AniList se ha excedido. Por favor intenta de nuevo en $seconds segundos.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function game(Neo4jService $neo4jService)
    {
        $neo = $neo4jService->client();
        $favMediaIds = session('wyr_fav_media', []);
        $favCharIds = session('wyr_fav_chars', []);

        if (empty($favMediaIds) && empty($favCharIds)) {
            return redirect()->route('wyr.index')->with('error', 'No hay favoritos cargados en la sesión.');
        }

        // Fetch favorite medias from Neo4j
        $medias = [];
        if (!empty($favMediaIds)) {
            $mediaResult = $neo->run('MATCH (m:Media) WHERE m.id IN $ids RETURN m', ['ids' => $favMediaIds]);
            foreach ($mediaResult as $row) {
                $props = $row->get('m')->getProperties()->toArray();
                $medias[] = [
                    'id' => $props['id'] ?? 0,
                    'title' => $props['title'] ?? $props['native'] ?? 'Unknown',
                    'image' => $props['coverImage'] ?? null,
                    'type' => 'Media'
                ];
            }
        }

        // Fetch favorite characters from Neo4j
        $characters = [];
        if (!empty($favCharIds)) {
            $charResult = $neo->run('MATCH (c:Character) WHERE c.id IN $ids RETURN c', ['ids' => $favCharIds]);
            foreach ($charResult as $row) {
                $props = $row->get('c')->getProperties()->toArray();
                $characters[] = [
                    'id' => $props['id'] ?? 0,
                    'name' => $props['name'] ?? 'Unknown',
                    'image' => $props['image'] ?? null,
                    'type' => 'Character'
                ];
            }
        }

        return view('would_you_rather.game', [
            'medias' => json_encode($medias),
            'characters' => json_encode($characters)
        ]);
    }

    public function progress($batchId)
    {
        $total = Cache::get("batch_{$batchId}_total", 0);
        $done = Cache::get("batch_{$batchId}_done", 0);
        $error = Cache::get("batch_{$batchId}_error", 0);
        
        $resumeTimestamp = Cache::get('wyr_rate_limit_alert');
        $resumeIn = 0;
        if ($resumeTimestamp && $resumeTimestamp > time()) {
            $resumeIn = $resumeTimestamp - time();
        }

        return response()->json([
            'total' => (int) $total,
            'done' => (int) $done,
            'error' => (int) $error,
            'resumeIn' => $resumeIn
        ]);
    }
}
