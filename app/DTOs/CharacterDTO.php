<?php

namespace App\DTOs;

class CharacterDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly ?string $image,
        /** @var string[] */
        public readonly array   $mediaIds   = [],
        public readonly ?string $mediaTitle = null,
        public readonly ?string $role       = null,
        public readonly int     $priority   = 0,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            id:         (int)    ($data['id']         ?? 0),
            name:       (string) ($data['name']       ?? ''),
            image:                $data['image']       ?? null,
            mediaIds:             $data['mediaIds']    ?? [],
            mediaTitle:           $data['mediaTitle']  ?? null,
            role:                 $data['role']        ?? null,
            priority:   (int)    ($data['priority']   ?? 0),
        );
    }
}
