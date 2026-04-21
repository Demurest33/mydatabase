<?php

namespace App\Actions;

use App\Services\Neo4jService;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
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
        array $characterIds
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
        }

        // Crear Nodo Asset y relacionarlo con TODOS los personajes
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
            
            WITH a, $characterIds as listIds
            UNWIND listIds as charId
            MATCH (c:Character {id: charId})
            CREATE (c)-[:HAS_ASSET]->(a)
            RETURN count(c)
        ';

        $client->run($query, [
            'characterIds' => $characterIds,
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

        return count($characterIds);
    }
}
