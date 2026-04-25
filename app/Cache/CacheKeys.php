<?php

namespace App\Cache;

use Illuminate\Support\Facades\Cache;
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

    /** Flat list of all franchise names (used in sidebars/dropdowns). */
    public const FRANCHISE_NAMES = 'franchises.names';

    /** All franchises for the public catalogue. */
    public const FRANCHISES_CATALOGUE = 'franchises.catalogue';

    /** All characters grouped by franchise/role. */
    public const CHARACTERS_GROUPED = 'characters.grouped';

    /** Franchise→media map for the characters sidebar filter. */
    public const CHARACTERS_FRANCHISE_MEDIA = 'characters.franchise_media';

    /** Asset type counts for the home page sidebar. */
    public const ASSETS_CATEGORIES = 'assets.categories';

    /** Customizable images per asset type (managed from backoffice). */
    public const ASSET_TYPE_IMAGES = 'assets.type_images';

    // ── Per-resource detail keys ─────────────────────────────────────────────

    /** Timeline & detail for one franchise. */
    public static function franchiseDetail(string $name): string
    {
        return 'franchises.detail.' . Str::slug($name);
    }

    /** Full detail for one media item. */
    public static function mediaDetail(int $id): string
    {
        return 'media.detail.' . $id;
    }

    /** Full detail for one character (show page). */
    public static function characterDetail(int $id): string
    {
        return 'characters.detail.' . $id;
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
            self::FRANCHISE_NAMES,
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
    public static function onMediaChange(string $franchiseName = '', int $mediaId = 0): array
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

        if ($mediaId) {
            $keys[] = self::mediaDetail($mediaId);
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

    /** Forget multiple keys — works on any cache driver including database. */
    public static function forget(array $keys): void
    {
        foreach (array_unique($keys) as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Keys to forget when a new Asset is created and linked.
     * Invalidates detail caches for every media and character it was linked to,
     * plus aggregate caches that include asset counts.
     */
    public static function onAssetCreate(array $mediaIds = [], array $characterIds = []): array
    {
        $keys = [
            self::ASSETS_CATEGORIES,
            self::FRANCHISES_CATALOGUE,   // shows asset counts per franchise
        ];

        foreach ($mediaIds as $id) {
            $keys[] = self::mediaDetail((int) $id);
        }

        foreach ($characterIds as $id) {
            $keys[] = self::characterDetail((int) $id);
        }

        return array_values(array_unique($keys));
    }
}
