<?php

namespace App\Cache;

use Illuminate\Support\Str;

/**
 * Central registry of cache keys and TTLs.
 *
 * Keeping keys here avoids typo-bugs and makes it easy to grep
 * every place a particular key is read or forgotten.
 */
final class CacheKeys
{
    // TTLs
    public const TTL_LONG  = 21_600;  // 6 hours  — public catalogue pages
    public const TTL_SHORT =  3_600;  // 1 hour   — detail pages

    // ── Public catalogue ─────────────────────────────────────────────────────

    /** All franchises for the public catalogue. */
    public const FRANCHISES_CATALOGUE = 'franchises.catalogue';

    /** All characters grouped by franchise/role. */
    public const CHARACTERS_GROUPED = 'characters.grouped';

    /** Franchise→media map for the characters sidebar filter. */
    public const CHARACTERS_FRANCHISE_MEDIA = 'characters.franchise_media';

    // ── Per-resource detail keys ─────────────────────────────────────────────

    /** Timeline & detail for one franchise. */
    public static function franchiseDetail(string $name): string
    {
        return 'franchises.detail.' . Str::slug($name);
    }

    // ── Admin catalogue keys ─────────────────────────────────────────────────

    /** All media grouped (admin backoffice). */
    public const ADMIN_MEDIA_GROUPED = 'admin.media.grouped';

    /** All characters grouped (admin backoffice). */
    public const ADMIN_CHARACTERS_GROUPED = 'admin.characters.grouped';

    /** All characters franchise→media map (admin backoffice). */
    public const ADMIN_CHARACTERS_FRANCHISE_MEDIA = 'admin.characters.franchise_media';

    /** All franchises (admin backoffice). */
    public const ADMIN_FRANCHISES_INDEX = 'admin.franchises.index';

    // ── Invalidation groups ──────────────────────────────────────────────────

    /**
     * Keys to forget when any Franchise changes.
     * Returns an array of string keys (plus a callable for parameterised keys).
     */
    public static function onFranchiseChange(string $name): array
    {
        return [
            self::FRANCHISES_CATALOGUE,
            self::ADMIN_FRANCHISES_INDEX,
            self::CHARACTERS_GROUPED,
            self::CHARACTERS_FRANCHISE_MEDIA,
            self::ADMIN_CHARACTERS_GROUPED,
            self::ADMIN_CHARACTERS_FRANCHISE_MEDIA,
            self::franchiseDetail($name),
        ];
    }

    /** Keys to forget when any Media changes. */
    public static function onMediaChange(string $franchiseName = ''): array
    {
        $keys = [
            self::FRANCHISES_CATALOGUE,
            self::ADMIN_FRANCHISES_INDEX,
            self::ADMIN_MEDIA_GROUPED,
            self::CHARACTERS_FRANCHISE_MEDIA,
            self::ADMIN_CHARACTERS_FRANCHISE_MEDIA,
        ];

        if ($franchiseName) {
            $keys[] = self::franchiseDetail($franchiseName);
        }

        return $keys;
    }

    /** Keys to forget when any Character changes. */
    public static function onCharacterChange(): array
    {
        return [
            self::CHARACTERS_GROUPED,
            self::CHARACTERS_FRANCHISE_MEDIA,
            self::ADMIN_CHARACTERS_GROUPED,
            self::ADMIN_CHARACTERS_FRANCHISE_MEDIA,
            self::FRANCHISES_CATALOGUE,
            self::ADMIN_FRANCHISES_INDEX,
        ];
    }
}
