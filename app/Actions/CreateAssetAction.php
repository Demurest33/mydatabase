<?php

namespace App\Actions;

use App\Cache\CacheKeys;
use App\Services\Neo4jService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class CreateAssetAction
{
    protected Neo4jService $neo4j;

    public function __construct(Neo4jService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    public function execute(
        ?UploadedFile $file,
        ?string $url,
        ?string $title,
        string $assetType,
        ?UploadedFile $coverImage,
        array $characterIds,
        array $mediaIds = [],
        ?string $remoteCoverUrl = null
    ): int {
        $client = $this->neo4j->client();
        $assetId = Str::uuid()->toString();
        
        $filename = null;
        $mimeType = 'text/html';
        $coverFilename = null;

        if ($file) {
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            
            $file->storeAs('assets', $filename, 'public');
            if (!$title) {
                $title = pathinfo($originalName, PATHINFO_FILENAME);
            }
        } else if ($url) {
            if (!$title) {
                $title = parse_url($url, PHP_URL_HOST) ?: 'Enlace Externo';
            }
        } else {
            throw new Exception('Debes proporcionar un archivo o una URL válida.');
        }

        if ($coverImage) {
            $coverExt = $coverImage->getClientOriginalExtension();
            $coverOriginalName = $coverImage->getClientOriginalName();
            $coverFilename = time() . '_cover_' . Str::slug(pathinfo($coverOriginalName, PATHINFO_FILENAME)) . '.' . $coverExt;
            $coverImage->storeAs('assets/covers', $coverFilename, 'public');
        } elseif ($remoteCoverUrl) {
            $imgResponse = Http::timeout(10)->get($remoteCoverUrl);
            if ($imgResponse->ok()) {
                $ext = pathinfo(parse_url($remoteCoverUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $coverFilename = time() . '_cover_remote.' . $ext;
                Storage::disk('public')->put('assets/covers/' . $coverFilename, $imgResponse->body());
            }
        }

        // Crear Nodo Asset
        $query = '
            MERGE (st:Storage {id: $storageId})
            ON CREATE SET st.name = $storageName, st.type = $storageType, st.basePath = $basePath, st.driver = $driver
            
            CREATE (a:Asset {
                id: $assetId,
                title: $title,
                filename: $filename,
                coverFilename: $coverFilename,
                url: $url,
                mimeType: $mimeType,
                type: $assetType,
                createdAt: datetime(),
                visibility: "public"
            })
            CREATE (a)-[:STORED_IN]->(st)
        ';

        $client->run($query, [
            'assetId' => $assetId,
            'title' => $title,
            'filename' => $filename,
            'coverFilename' => $coverFilename,
            'url' => $url,
            'mimeType' => $mimeType,
            'assetType' => $assetType,
            'storageId' => $filename ? "local_storage" : "web_storage",
            'storageName' => $filename ? "Local Server" : "External Web",
            'storageType' => $filename ? "LOCAL" : "REMOTE",
            'basePath' => $filename ? "/storage/assets" : "",
            'driver' => $filename ? "local" : "url"
        ]);

        $linkedCount = 0;

        if (!empty($characterIds)) {
            $client->run('
                UNWIND $ids as charId
                MATCH (a:Asset {id: $assetId})
                MATCH (c:Character {id: toInteger(charId)})
                MERGE (c)-[:HAS_ASSET]->(a)
            ', ['ids' => $characterIds, 'assetId' => $assetId]);
            $linkedCount += count($characterIds);
        }

        if (!empty($mediaIds)) {
            $client->run('
                UNWIND $ids as mId
                MATCH (a:Asset {id: $assetId})
                MATCH (m:Media {id: toInteger(mId)})
                MERGE (m)-[:HAS_ASSET]->(a)
            ', ['ids' => $mediaIds, 'assetId' => $assetId]);
            $linkedCount += count($mediaIds);
        }

        CacheKeys::forget(CacheKeys::onAssetCreate($mediaIds, $characterIds));

        return $linkedCount;
    }
}
