<?php

namespace App\DTOs;

class TagDTO
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $type,
        public readonly string $category,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            id:       (int)    ($data['id']       ?? 0),
            name:     (string) ($data['name']     ?? ''),
            slug:     (string) ($data['slug']     ?? ''),
            type:     (string) ($data['type']     ?? 'character'),
            category: (string) ($data['category'] ?? ''),
        );
    }
}
