<?php

namespace App\DTOs;

class FranchiseDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly int     $mediaCount      = 0,
        public readonly int     $characterCount  = 0,
        public readonly int     $assetCount      = 0,
        public readonly ?string $coverImage      = null,
        public readonly ?string $primaryFormat   = null,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            name:           (string) ($data['name'] ?? ''),
            mediaCount:     (int)    ($data['mediaCount']     ?? $data['media_count']      ?? 0),
            characterCount: (int)    ($data['characterCount'] ?? $data['charactersCount']  ?? 0),
            assetCount:     (int)    ($data['assetCount']     ?? $data['assetsCount']      ?? 0),
            coverImage:              $data['coverImage']  ?? null,
            primaryFormat:           $data['primaryFormat'] ?? null,
        );
    }
}
