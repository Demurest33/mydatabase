<?php

namespace App\DTOs;

class AlbumDTO
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $description,
        public readonly string $coverImage,
        public readonly bool   $isActive,
        public readonly int    $mediaCount,
        public readonly int    $characterCount,
    ) {}

    public static function from(array $props, int $mediaCount = 0, int $characterCount = 0): self
    {
        return new self(
            id:             (int) ($props['id'] ?? 0),
            name:           $props['name'] ?? '',
            slug:           $props['slug'] ?? '',
            description:    $props['description'] ?? '',
            coverImage:     $props['coverImage'] ?? '',
            isActive:       (bool) ($props['isActive'] ?? true),
            mediaCount:     $mediaCount,
            characterCount: $characterCount,
        );
    }
}
