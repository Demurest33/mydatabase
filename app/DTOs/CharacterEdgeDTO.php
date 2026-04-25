<?php

namespace App\DTOs;

/** Character with its role in the context of a specific Media entry. */
class CharacterEdgeDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly ?string $image,
        public readonly string  $role,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            id:    (int)    ($data['id']    ?? 0),
            name:  (string) ($data['name']  ?? ''),
            image: $data['image'] ?? null,
            role:  (string) ($data['role']  ?? 'SUPPORTING'),
        );
    }
}
