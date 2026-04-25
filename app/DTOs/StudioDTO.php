<?php

namespace App\DTOs;

class StudioDTO
{
    public function __construct(
        public readonly string $name,
    ) {}

    public static function from(array $data): self
    {
        return new self(name: (string) ($data['name'] ?? ''));
    }
}
