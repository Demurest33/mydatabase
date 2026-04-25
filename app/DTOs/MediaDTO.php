<?php

namespace App\DTOs;

class MediaDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $title,
        public readonly ?string $native      = null,
        public readonly ?string $format      = null,
        public readonly ?string $status      = null,
        public readonly ?string $description = null,
        public readonly ?string $coverImage  = null,
        public readonly ?string $bannerImage = null,
        public readonly ?int    $startYear   = null,
        public readonly ?int    $startMonth  = null,
        public readonly ?int    $startDay    = null,
        public readonly ?float  $score       = null,
        public readonly ?string $season      = null,
        public readonly ?int    $seasonYear  = null,
        public readonly ?string $type        = null,
        public readonly ?string $tag         = null,
        /** @var string[] */
        public readonly array   $genres      = [],
        /** @var StudioDTO[] */
        public readonly array   $studios     = [],
        /** @var CharacterEdgeDTO[] */
        public readonly array   $characters  = [],
    ) {}

    /**
     * Hydrate from raw Neo4j node properties.
     *
     * @param  StudioDTO[]        $studios
     * @param  CharacterEdgeDTO[] $characters
     */
    public static function from(
        array $p,
        array $genres     = [],
        array $studios    = [],
        array $characters = [],
    ): self {
        return new self(
            id:          (int)    ($p['id']          ?? 0),
            title:       (string) ($p['title']        ?? ''),
            native:                $p['native']        ?? null,
            format:                $p['format']        ?? null,
            status:                $p['status']        ?? null,
            description:           $p['description']   ?? null,
            coverImage:            $p['coverImage']    ?? null,
            bannerImage:           $p['bannerImage']   ?? null,
            startYear:  isset($p['start_year'])  ? (int)   $p['start_year']  : null,
            startMonth: isset($p['start_month']) ? (int)   $p['start_month'] : null,
            startDay:   isset($p['start_day'])   ? (int)   $p['start_day']   : null,
            score:      isset($p['score'])       ? (float) $p['score']       : null,
            season:                $p['season']        ?? null,
            seasonYear: isset($p['year'])        ? (int)   $p['year']        : null,
            type:                  $p['type']          ?? null,
            tag:                   $p['tag']           ?? null,
            genres:     $genres,
            studios:    $studios,
            characters: $characters,
        );
    }
}
