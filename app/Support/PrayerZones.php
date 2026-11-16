<?php

namespace App\Support;

use App\Models\SiteSetting;
use Throwable;

/** Resolves the JAKIM prayer zone chosen in the admin dashboard, falling back to JAKIM_PRAYER_ZONE. */
class PrayerZones
{
    public static function current(): string
    {
        $fallback = strtoupper((string) config('dashboard.prayer_zone', 'WLY01'));
        try {
            $chosen = strtoupper((string) SiteSetting::current()->prayer_zone);
        } catch (Throwable) {
            // Column not migrated yet, or no database: keep working with the configured zone.
            return $fallback;
        }

        return self::isValid($chosen) ? $chosen : $fallback;
    }

    /** @return array<string, array<string, string>> */
    public static function grouped(): array
    {
        return config('dashboard.prayer_zones', []);
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return collect(self::grouped())->flatMap(fn ($zones) => array_keys($zones))->values()->all();
    }

    public static function isValid(string $code): bool
    {
        return in_array($code, self::codes(), true);
    }

    /** @return array{code:string,state:string,areas:string}|null */
    public static function describe(string $code): ?array
    {
        foreach (self::grouped() as $state => $zones) {
            if (isset($zones[$code])) {
                return ['code' => $code, 'state' => $state, 'areas' => $zones[$code]];
            }
        }

        return null;
    }
}
