<?php

namespace App\DTOs;

/** Represents one media appearance of a character (used for grouping/filtering). */
class AppearanceDTO
{
    public function __construct(
        public readonly string $mediaId,
        public readonly string $mediaTitle,
        public readonly string $role,
        public readonly string $franchise,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            mediaId:    (string) ($data['mediaId']    ?? ''),
            mediaTitle: (string) ($data['mediaTitle'] ?? ''),
            role:       (string) ($data['role']       ?? 'UNKNOWN'),
            franchise:  (string) ($data['franchise']  ?? ''),
        );
    }
}
