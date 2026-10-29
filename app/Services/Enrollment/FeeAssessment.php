<?php

namespace App\Services\Enrollment;

use Illuminate\Support\Collection;

use App\Models\AssessmentDB\StudentFee;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Fee assessment of an enrollment, computed on the server (the browser only displays it).
 *
 * Fee lines come from the Assessment Office's template (assessment.student_fee) for the program,
 * year level, term and campus. A template amount of 0 means "computed":
 *   TUITION ...       = total lecture fees of the subjects
 *   LAB FEE           = total lab fees of the subjects
 *   IT FEE            = 500 when a subject is an IT subject
 * plus DEVELOPMENTAL FEE gets the subjects' total dev fee added, and these rules:
 *   - Shiftee (transferee 2): no ENTRANCE FEE and SCHOOL ID FEE; also no ADMISSION FEE unless type 1 (new)
 *   - HAND BOOK FEE is not charged to an Irregular Transferee (status 2 + transferee 1) or to type 2
 *   - Irregular (status 2): LIBRARY FEE is 300
 * Subject fee totals are in whole pesos, like the old system.
 */
class FeeAssessment
{
    public const IT_FEE = 500;
    public const IRREGULAR_LIBRARY_FEE = 300;

    private const STATUS_IRREGULAR = 2;
    private const TYPE_NEW = 1;
    private const TYPE_OLD = 2;
    private const TRANSFEREE = 1;
    private const SHIFTEE = 2;

    /**
     * @param  Collection  $subjects  offered subjects (subUnit, lecFee, labFee, devFee, itfee)
     * @return array{totals: array, lines: array}  lines: [fund, account, amount, saved]
     */
    public static function assess(
        string $programCode,
        int $yearLevel,
        ConfigureCurrent $term,
        string $campus,
        Collection $subjects,
        int $studStatus,
        int $studType,
        int $transferee
    ): array {
        $totals = [
            'units' => (int) $subjects->sum('subUnit'),
            'tuition' => round((float) $subjects->sum('lecFee')),
            'lab' => round((float) $subjects->sum('labFee')),
            'dev' => round((float) $subjects->sum('devFee')),
        ];
        $hasItSubject = $subjects->contains(fn ($subject) => $subject->itfee === 'Yes');

        $fees = StudentFee::where('prog_Code', $programCode)
            ->where('yrlevel', $yearLevel)
            ->where('schlyear', $term->schlyear)
            ->where('semester', $term->semester)
            ->where('campus', $campus)
            ->orderBy('id')
            ->get();

        $lines = [];
        foreach ($fees as $fee) {
            $account = strtoupper(trim($fee->accountName));

            if (self::isExcluded($account, $studStatus, $studType, $transferee)) {
                continue;
            }

            $isComputed = (float) $fee->amountFee == 0;
            $amount = $isComputed ? self::computedAmount($account, $totals, $hasItSubject) : (float) $fee->amountFee;

            if ($account === 'DEVELOPMENTAL FEE') {
                $amount += $totals['dev'];
            }
            if ($studStatus === self::STATUS_IRREGULAR && $account === 'LIBRARY FEE') {
                $amount = self::IRREGULAR_LIBRARY_FEE;
            }

            $lines[] = [
                'fund' => $fee->fundname_code,
                'account' => $fee->accountName,
                'amount' => $amount,
                // Shown but not saved: other "computed" lines that stay 0
                'saved' => ! $isComputed || self::isComputedAccount($account),
            ];
        }

        return ['totals' => $totals, 'lines' => $lines, 'total' => array_sum(array_column($lines, 'amount'))];
    }

    private static function isExcluded(string $account, int $studStatus, int $studType, int $transferee): bool
    {
        if ($transferee === self::SHIFTEE) {
            $excluded = $studType === self::TYPE_NEW
                ? ['ENTRANCE FEE', 'SCHOOL ID FEE']
                : ['ADMISSION FEE', 'ENTRANCE FEE', 'SCHOOL ID FEE'];

            if (in_array($account, $excluded, true)) {
                return true;
            }
        }

        $noHandbook = ($studStatus === self::STATUS_IRREGULAR && $transferee === self::TRANSFEREE) || $studType === self::TYPE_OLD;

        return $noHandbook && $account === 'HAND BOOK FEE';
    }

    // Amount of a template line of 0: taken from the subjects
    private static function computedAmount(string $account, array $totals, bool $hasItSubject): float
    {
        return match (true) {
            str_starts_with($account, 'TUITION') => $totals['tuition'],
            $account === 'LAB FEE' => $totals['lab'],
            $account === 'IT FEE' => $hasItSubject ? self::IT_FEE : 0,
            default => 0,
        };
    }

    private static function isComputedAccount(string $account): bool
    {
        return str_starts_with($account, 'TUITION') || in_array($account, ['LAB FEE', 'IT FEE'], true);
    }
}
