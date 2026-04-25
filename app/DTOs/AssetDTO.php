<?php

namespace App\DTOs;

class AssetDTO
{
    public readonly string  $fileUrl;
    public readonly ?string $coverUrl;

    public function __construct(
        public readonly ?int    $id,
        public readonly string  $type,
        public readonly ?string $title,
        public readonly ?string $url,
        public readonly ?string $filename,
        public readonly ?string $coverFilename,
        public readonly ?string $storageName,
        public readonly ?string $createdAt,
    ) {
        $this->fileUrl  = $this->url
            ?? ($this->filename ? asset('storage/assets/' . $this->filename) : '');

        $this->coverUrl = $this->coverFilename
            ? asset('storage/assets/covers/' . $this->coverFilename)
            : null;
    }

    public static function from(array $data): self
    {
        return new self(
            id:             isset($data['id']) ? (int) $data['id'] : null,
            type:           (string) ($data['type']     ?? 'FILE'),
            title:          $data['title']          ?? null,
            url:            $data['url']             ?? null,
            filename:       $data['filename']        ?? null,
            coverFilename:  $data['coverFilename']   ?? null,
            storageName:    $data['storageName']     ?? null,
            createdAt:      $data['createdAtStr']    ?? $data['createdAt'] ?? null,
        );
    }
}
