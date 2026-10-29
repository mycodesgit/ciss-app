<?php

namespace App\Services\Enrollment;

use Illuminate\Support\Collection;

use App\Models\AdmissionDB\User;
use App\Models\EnrollmentDB\GradeCode;

/**
 * Grade rules of the Gradesheet (same as the old system):
 *  - which grade codes a user may encode (by role / campus)
 *  - credits earned: the subject's units, or 0 for INC / NN / NG / Drp. / no grade / a failing grade (4.0 or 5.0)
 *  - completion is only for an INC / FAILURE final grade
 *  - the GPA equivalent shown next to a grade (students who entered 2021 or earlier use the old scale)
 */
class GradeRules
{
    // Grades that earn no credit
    public const NO_CREDIT = ['INC', 'NN', 'NG', 'Drp.'];

    // Final grades that take a completion grade
    public const NEEDS_COMPLETION = ['FAILURE', 'INC', 'Inc.', 'inc'];

    // Shown in red
    public const WARNING = ['INC', 'Inc.', 'inc', 'NN', 'nn'];

    private const GRADUATE_ROLE = 15;
    private const MAIN_CAMPUS_REGISTRAR_ROLES = [0, 3, 4];
    private const LAST_OLD_SCALE_YEAR = 2021;

    // gradecode rows the user may pick, in the old order (percentages 44-74 first, highest first)
    public static function codesFor(User $user): Collection
    {
        if ((int) $user->role === self::GRADUATE_ROLE) {
            $ids = array_merge(range(2, 43), [76]);
        } elseif ($user->campus === 'MC' && in_array((int) $user->role, self::MAIN_CAMPUS_REGISTRAR_ROLES, true)) {
            $ids = array_merge([2, 3, 4, 6, 7, 8, 9, 10, 12, 13, 15, 16, 17, 19, 20, 21, 22, 23, 25, 26, 27, 28, 29, 31, 32], range(44, 80));
        } else {
            $ids = range(44, 80);
        }

        return GradeCode::whereIn('id', $ids)
            ->orderByRaw('CASE WHEN id BETWEEN 44 AND 74 THEN id END DESC, id DESC')
            ->pluck('grade')
            ->map(fn ($grade) => (string) $grade)
            ->values();
    }

    // The subject's units, or 0 for no grade, INC / NN / NG / Drp. and a failing grade
    public static function credit(?string $grade, $units): float
    {
        if ($grade === null || $grade === '' || in_array($grade, self::NO_CREDIT, true) || self::isFailing($grade)) {
            return 0;
        }

        return (float) $units;
    }

    /**
     * Failing = an equivalent of 4.0 (Conditional) or 5.0 (Failure):
     *  - FAILURE
     *  - a percentage below 75 (both grading scales give 4.0 / 5.0 there)
     *  - a GPA-style grade of 4 or more: 4, 4.0, 4.00, 5, 5.0, 5.00 (1, 2, 3 are 1.0 / 2.0 / 3.0)
     */
    public static function isFailing(?string $grade): bool
    {
        if (strtoupper((string) $grade) === 'FAILURE') {
            return true;
        }
        if (! is_numeric($grade)) {
            return false;
        }

        $value = (float) $grade;
        $isPercentage = ! str_contains($grade, '.') && $value > 5;

        return $isPercentage ? $value < 75 : $value >= 4;
    }

    public static function needsCompletion(?string $grade): bool
    {
        return in_array($grade, self::NEEDS_COMPLETION, true);
    }

    public static function isWarning(?string $grade): bool
    {
        return in_array($grade, self::WARNING, true);
    }

    // The GPA shown for a whole-number percentage grade; other grades (INC, 1.25, ...) are shown as they are
    public static function equivalent(?string $grade, string $studentId): ?string
    {
        if ($grade === null || $grade === '' || ! is_numeric($grade) || str_contains($grade, '.')) {
            return $grade;
        }

        $value = (int) $grade;
        $oldScale = (int) substr($studentId, 0, 4) <= self::LAST_OLD_SCALE_YEAR;

        return $oldScale ? self::oldScale($value) : self::newScale($value);
    }

    // Students who entered 2021 or earlier: 0.1 steps
    private static function oldScale(int $grade): string
    {
        if ($grade >= 95 || $grade === 1) return '1.0';
        if ($grade >= 86) return '1.' . (95 - $grade);           // 94 -> 1.1 ... 86 -> 1.9
        if ($grade >= 85 || $grade === 2) return '2.0';
        if ($grade >= 76) return '2.' . (85 - $grade);           // 84 -> 2.1 ... 76 -> 2.9
        if ($grade >= 75 || $grade === 3) return '3.0';
        if ($grade >= 70) return '4.0';

        return '5.0';
    }

    private static function newScale(int $grade): string
    {
        return match (true) {
            $grade >= 97 || $grade === 1 => '1.00',
            $grade >= 94 => '1.25',
            $grade >= 91 => '1.50',
            $grade >= 88 => '1.75',
            $grade >= 85 || $grade === 2 => '2.00',
            $grade >= 82 => '2.25',
            $grade >= 79 => '2.50',
            $grade >= 76 => '2.75',
            $grade >= 75 || $grade === 3 => '3.00',
            $grade >= 70 => '4.00',
            default => '5.00',
        };
    }
}
