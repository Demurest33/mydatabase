<?php

namespace App\Http\Controllers\Admin;

use App\Cache\CacheKeys;
use App\Http\Controllers\Controller;
use App\Services\Neo4jService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoTagController extends Controller
{
    // ── Danbooru label → our tag name ─────────────────────────────────────────
    private const DANBOORU_MAP = [
        // Hair Color
        'blonde hair'       => 'Blonde',
        'brown hair'        => 'Brown Hair',
        'black hair'        => 'Black Hair',
        'red hair'          => 'Red Hair',
        'white hair'        => 'White Hair',
        'silver hair'       => 'Silver Hair',
        'grey hair'         => 'Gray Hair',
        'gray hair'         => 'Gray Hair',
        'pink hair'         => 'Pink Hair',
        'blue hair'         => 'Blue Hair',
        'green hair'        => 'Green Hair',
        'purple hair'       => 'Purple Hair',
        'orange hair'       => 'Orange Hair',
        'aqua hair'         => 'Teal Hair',
        'teal hair'         => 'Teal Hair',
        'multicolored hair' => 'Multicolor Hair',
        'rainbow hair'      => 'Multicolor Hair',
        'bald'              => 'Bald',

        // Hair Length
        'short hair'        => 'Short Hair',
        'medium hair'       => 'Medium Hair',
        'long hair'         => 'Long Hair',
        'very long hair'    => 'Very Long Hair',

        // Eye Color
        'blue eyes'         => 'Blue Eyes',
        'green eyes'        => 'Green Eyes',
        'brown eyes'        => 'Brown Eyes',
        'black eyes'        => 'Black Eyes',
        'red eyes'          => 'Red Eyes',
        'purple eyes'       => 'Purple Eyes',
        'yellow eyes'       => 'Yellow Eyes',
        'grey eyes'         => 'Gray Eyes',
        'gray eyes'         => 'Gray Eyes',
        'pink eyes'         => 'Pink Eyes',
        'heterochromia'     => 'Heterochromia',

        // Gender
        '1girl'             => 'Female',
        '1boy'              => 'Male',

        // Body Type
        'small breasts'     => 'Small Breasts',
        'medium breasts'    => 'Large Breasts',
        'large breasts'     => 'Large Breasts',
        'big breasts'       => 'Large Breasts',
        'huge breasts'      => 'Huge Breasts',
        'muscular'          => 'Muscular',
        'fat'               => 'Chubby',
        'skinny'            => 'Slender',

        // Features
        'cat ears'          => 'Nekomimi',
        'fox ears'          => 'Fox Ears',
        'pointy ears'       => 'Elf Ears',
        'elf ears'          => 'Elf Ears',
        'horns'             => 'Horns',
        'demon horns'       => 'Horns',
        'tail'              => 'Tail',
        'wings'             => 'Wings',
        'angel wings'       => 'Wings',
        'demon wings'       => 'Wings',
        'fangs'             => 'Fangs',

        // Accessories
        'glasses'           => 'Glasses',
        'sunglasses'        => 'Sunglasses',
        'eyepatch'          => 'Eyepatch',
        'mask'              => 'Mask',
        'hat'               => 'Hat',
        'hair ribbon'       => 'Hair Ribbon',
        'ahoge'             => 'Ahoge',
    ];

    public function __construct(protected Neo4jService $neo4j) {}

    public function index()
    {
        $client = $this->neo4j->client();

        $characters = [];
        foreach ($client->run(
            'MATCH (c:Character)
             WHERE c.image IS NOT NULL AND c.image <> ""
             OPTIONAL MATCH (m:Media)-[:HAS_CHARACTER]->(c)
             OPTIONAL MATCH (f:Franchise)-[:HAS_ENTRY]->(m)
             OPTIONAL MATCH (c)-[:HAS_TAG]->(t:Tag)
             WITH c,
                  collect(DISTINCT f.name) AS franchises,
                  collect(CASE WHEN t IS NOT NULL THEN {id: toString(t.id), name: t.name} ELSE null END) AS rawTags
             WITH c, franchises, [x IN rawTags WHERE x IS NOT NULL] AS tags
             RETURN c, franchises, tags, size(tags) AS tagCount
             ORDER BY tagCount ASC, c.name ASC
             LIMIT 500'
        ) as $record) {
            $props              = $record->get('c')->getProperties()->toArray();
            $props['tags']      = [];
            $props['franchise'] = '';

            $franchises = [];
            foreach ($record->get('franchises') as $f) {
                if ($f !== null) $franchises[] = (string) $f;
            }
            $props['franchise'] = $franchises[0] ?? '';

            foreach ($record->get('tags') as $t) {
                $props['tags'][] = ['id' => (string)($t['id'] ?? ''), 'name' => (string)($t['name'] ?? '')];
            }
            $props['tagCount'] = (int) $record->get('tagCount');
            $characters[] = $props;
        }

        // Unique sorted franchise list for the filter
        $franchises = collect($characters)
            ->pluck('franchise')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $r = $client->run(
            'MATCH (c:Character) WHERE c.image IS NOT NULL AND c.image <> ""
             RETURN count(c) AS total,
                    sum(CASE WHEN NOT (c)-[:HAS_TAG]->() THEN 1 ELSE 0 END) AS untagged'
        )->first();

        $totalWithImage = (int) ($r?->get('total')   ?? 0);
        $totalUntagged  = (int) ($r?->get('untagged') ?? 0);

        $backend = env('AUTO_TAG_BACKEND', 'wd14_local');

        return view('admin.auto-tag.index', compact(
            'characters', 'franchises', 'totalWithImage', 'totalUntagged', 'backend'
        ));
    }

    public function process(Request $request, int $id): JsonResponse
    {
        $client  = $this->neo4j->client();
        $result  = $client->run('MATCH (c:Character {id: $id}) RETURN c', ['id' => $id]);

        if ($result->isEmpty()) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $c     = $result->first()->get('c')->getProperties()->toArray();
        $image = trim($c['image'] ?? '');

        if (empty($image) || !filter_var($image, FILTER_VALIDATE_URL)) {
            return response()->json(['skipped' => true, 'reason' => 'no_image']);
        }

        $allTags = $this->loadCharacterTagsFlat();
        if (empty($allTags)) {
            return response()->json(['error' => 'No character tags defined'], 500);
        }

        $b64 = $this->fetchImageAsBase64($image);
        if ($b64 === null) {
            return response()->json(['skipped' => true, 'reason' => 'image_fetch_failed']);
        }

        $backend = env('AUTO_TAG_BACKEND', 'wd14_local');
        $tagIds  = match ($backend) {
            'ollama'    => $this->analyzeWithOllama($b64, $allTags),
            'wd14'      => $this->analyzeWithWD14HF($b64, $allTags),
            default     => $this->analyzeWithWD14Local($b64, $allTags),
        };

        if ($tagIds === null) {
            return response()->json(['error' => "Error con backend '{$backend}'. ¿Está disponible?"], 500);
        }

        if (!empty($tagIds)) {
            $client->run(
                'MATCH (c:Character {id: $id})
                 UNWIND $tagIds AS tagId
                 MATCH (t:Tag {id: tagId})
                 MERGE (c)-[:HAS_TAG]->(t)',
                ['id' => $id, 'tagIds' => $tagIds]
            );
            CacheKeys::forget(CacheKeys::onCharacterChange());
        }

        $assigned = array_values(array_filter($allTags, fn($t) => in_array($t['id'], $tagIds)));

        return response()->json(['tagged' => $assigned, 'count' => count($tagIds)]);
    }

    // ── WD14 via local Python server (tagger_server.py) ──────────────────────

    private function analyzeWithWD14Local(string $b64, array $tags): ?array
    {
        $serverUrl = rtrim(env('WD14_LOCAL_URL', 'http://localhost:7860'), '/');
        $thresh    = (float) env('WD14_THRESHOLD', 0.35);
        $nameToId  = array_column($tags, 'id', 'name');

        try {
            $response = Http::timeout(60)
                ->post("{$serverUrl}/tag", [
                    'image'     => $b64,
                    'threshold' => $thresh,
                ]);

            if (!$response->successful()) {
                Log::error('WD14 local error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $predictions = $response->json();
            if (!is_array($predictions)) return null;

            $tagIds = [];
            foreach ($predictions as $pred) {
                $label = strtolower(str_replace('_', ' ', $pred['label'] ?? ''));
                if (isset(self::DANBOORU_MAP[$label], $nameToId[self::DANBOORU_MAP[$label]])) {
                    $tagIds[] = $nameToId[self::DANBOORU_MAP[$label]];
                }
            }

            return array_values(array_unique($tagIds));

        } catch (\Exception $e) {
            Log::error('WD14 local exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    // ── WD14 via Hugging Face Inference API (fallback) ────────────────────────

    private function analyzeWithWD14HF(string $b64, array $tags): ?array  // AUTO_TAG_BACKEND=wd14
    {
        $token   = env('HF_TOKEN', '');
        $model   = env('HF_WD14_MODEL', 'SmilingWolf/wd-vit-large-tagger-v3');
        $thresh  = (float) env('WD14_THRESHOLD', 0.35);

        $nameToId = array_column($tags, 'id', 'name');

        $headers = ['Content-Type' => 'application/octet-stream'];
        if ($token) $headers['Authorization'] = 'Bearer ' . $token;

        $imageBytes = base64_decode($b64);

        try {
            $response = Http::timeout(60)
                ->withHeaders($headers)
                ->withBody($imageBytes, 'application/octet-stream')
                ->post("https://api-inference.huggingface.co/models/{$model}");

            // Model may be loading (cold start) — wait and retry once
            if ($response->status() === 503) {
                sleep(15);
                $response = Http::timeout(60)
                    ->withHeaders($headers)
                    ->withBody($imageBytes, 'application/octet-stream')
                    ->post("https://api-inference.huggingface.co/models/{$model}");
            }

            if (!$response->successful()) {
                Log::error('WD14 error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $predictions = $response->json();
            if (!is_array($predictions)) return null;

            $tagIds = [];
            foreach ($predictions as $pred) {
                $score = (float) ($pred['score'] ?? 0);
                if ($score < $thresh) continue;

                // WD14 returns labels with underscores — normalize to spaces+lowercase
                $label = strtolower(str_replace('_', ' ', $pred['label'] ?? ''));

                if (isset(self::DANBOORU_MAP[$label], $nameToId[self::DANBOORU_MAP[$label]])) {
                    $tagIds[] = $nameToId[self::DANBOORU_MAP[$label]];
                }
            }

            return array_values(array_unique($tagIds));

        } catch (\Exception $e) {
            Log::error('WD14 exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    // ── Ollama (local) ────────────────────────────────────────────────────────

    private function analyzeWithOllama(string $b64, array $tags): ?array
    {
        $baseUrl = rtrim(env('OLLAMA_URL', 'http://localhost:11434'), '/');
        $model   = env('OLLAMA_MODEL', 'llava-phi3');
        $timeout = (int) env('OLLAMA_TIMEOUT', 300);

        $tagLines = implode("\n", array_map(
            fn($t) => "{$t['id']} | {$t['name']} | {$t['category']}",
            $tags
        ));

        $prompt = <<<EOT
Analyze this character image and identify which of the following tags clearly apply.

AVAILABLE TAGS (ID | Name | Category):
{$tagLines}

Rules:
- Only include tags for traits CLEARLY visible
- Hair Color: dominant color only; "Bald" if no hair
- Hair Length: short=above shoulder, medium=shoulder, long=below shoulder, very long=waist+
- Eye Color: only if visible
- Skin Tone: light/fair/tan/brown/dark — pick closest
- Gender: based on visual presentation
- Features/Accessories: only if unambiguous
- If uncertain, omit the tag

Respond ONLY with a valid JSON array of integer IDs. Example: [12345678, 87654321]
If nothing applies: []
EOT;

        try {
            $response = Http::timeout($timeout)
                ->post("{$baseUrl}/api/generate", [
                    'model'   => $model,
                    'prompt'  => $prompt,
                    'images'  => [$b64],
                    'stream'  => false,
                    'options' => ['temperature' => 0.1],
                ]);

            if (!$response->successful()) return null;

            $text = $response->json('response', '[]');
            preg_match('/\[\s*[\d,\s]*\]/', $text, $matches);
            if (empty($matches[0])) return [];

            $ids      = json_decode($matches[0], true);
            $validIds = array_column($tags, 'id');

            return array_values(array_filter(
                array_map('intval', is_array($ids) ? $ids : []),
                fn($id) => in_array($id, $validIds)
            ));

        } catch (\Exception $e) {
            Log::error('Ollama exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function loadCharacterTagsFlat(): array
    {
        $tags = [];
        foreach ($this->neo4j->client()->run(
            'MATCH (t:Tag {type: "character"}) RETURN t ORDER BY t.category ASC, t.name ASC'
        ) as $record) {
            $d      = $record->get('t')->getProperties()->toArray();
            $tags[] = [
                'id'       => (int)    $d['id'],
                'name'     => (string) $d['name'],
                'category' => (string) ($d['category'] ?? ''),
            ];
        }
        return $tags;
    }

    private function fetchImageAsBase64(string $url): ?string
    {
        try {
            $response = Http::timeout(20)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
            if (!$response->successful()) return null;
            return base64_encode($response->body());
        } catch (\Exception $e) {
            Log::warning('Image fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
