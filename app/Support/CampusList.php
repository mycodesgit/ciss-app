<?php

namespace App\Support;

/**
 * A campus column can hold a list: a transferred student's records get the new campus added ("MC, VC"),
 * the way coas-app does it (Transferred Student). These helpers read such a list.
 * Queries use ->whereCampusIn($column, $campuses) (registered in AppServiceProvider): an exact code in the
 * list, so "MC, VC" matches MC and VC, but "SCC" never matches CC.
 */
class CampusList
{
    // "MC, VC" -> ['MC', 'VC']
    public static function split(?string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    // The campus the student is at now: the last one added ("MC, VC" -> VC)
    public static function current(?string $value): ?string
    {
        $campuses = self::split($value);

        return $campuses ? end($campuses) : null;
    }

    public static function has(?string $value, string $campus): bool
    {
        return in_array($campus, self::split($value), true);
    }
}
