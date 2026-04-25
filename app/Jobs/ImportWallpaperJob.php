<?php

namespace App\Jobs;

use App\Actions\CreateAssetAction;
use App\Services\Neo4jService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WallpaperImportLog;
use App\Support\SteamHttp;
use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Log;

class ImportWallpaperJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function backoff(): array
    {
        return [30, 60, 120, 300]; // 30s → 1m → 2m → 5m between retries
    }

    public function __construct(
        private readonly string $workshopId,
        private readonly int    $mediaId,
    ) {}

    public function handle(Neo4jService $neo4j, CreateAssetAction $action): void
    {
        $url = "https://steamcommunity.com/sharedfiles/filedetails/?id={$this->workshopId}";

        // Skip if already imported
        $existing = $neo4j->client()->run(
            'MATCH (a:Asset {url: $url}) RETURN a LIMIT 1',
            ['url' => $url]
        );
        if (!$existing->isEmpty()) {
            return; // already imported, skip silently
        }

        $batchId = $this->batch()?->id ?? 'manual';

        // Scrape Steam Workshop page
        $response = SteamHttp::client()->get($url);

        if ($response->status() === 429) {
            $retryAfter = (int) ($response->header('Retry-After') ?: 60);
            $this->release($retryAfter); // back to queue, not a failure
            return;
        }

        if (!$response->ok()) {
            WallpaperImportLog::record($batchId, $this->workshopId, 'failed',
                "Steam devolvió HTTP {$response->status()}");
            $this->fail("Steam HTTP {$response->status()}");
            return;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($response->body(), 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        // Detect Steam error pages via div#message
        $messageDiv = $dom->getElementById('message');
        if ($messageDiv) {
            $h3 = $xpath->query('.//h3', $messageDiv);
            $errorText = $h3->length > 0
                ? trim($h3->item(0)->textContent)
                : trim($messageDiv->textContent);
            WallpaperImportLog::record($batchId, $this->workshopId, 'skipped',
                $errorText ?: 'Error de Steam');
            return;
        }

        // Fallback: generic error h2 (e.g. login required)
        $errorH2 = $xpath->query('//h2[contains(normalize-space(.),"Error")]');
        if ($errorH2->length > 0) {
            WallpaperImportLog::record($batchId, $this->workshopId, 'skipped',
                'Requiere inicio de sesión en Steam');
            return;
        }

        $titleNodes = $xpath->query('//*[contains(@class,"workshopItemTitle")]');
        $title      = $titleNodes->length > 0 ? trim($titleNodes->item(0)->textContent) : "Wallpaper {$this->workshopId}";

        $imgNode = $dom->getElementById('previewImage')
            ?? $dom->getElementById('previewImageMain');

        if (!$imgNode) {
            foreach (['workshopItemPreviewImageEnlargeable', 'workshopItemPreviewImageMain'] as $cls) {
                $nodes = $xpath->query("//*[contains(@class,'{$cls}')]");
                if ($nodes->length > 0 && $nodes->item(0) instanceof \DOMElement) {
                    $imgNode = $nodes->item(0);
                    break;
                }
            }
        }

        $imageUrl = $imgNode instanceof \DOMElement ? $imgNode->getAttribute('src') : null;

        $action->execute(
            file:           null,
            url:            $url,
            title:          $title,
            assetType:      'WALLPAPER ENGINE',
            coverImage:     null,
            characterIds:   [],
            mediaIds:       [$this->mediaId],
            remoteCoverUrl: $imageUrl,
        );
    }
}
