<?php

namespace App\Services\Scheduling;

use App\Models\AssessmentDB\AccountAppraisal;
use App\Models\ScheduleDB\Subject;

/**
 * Units and fees of a subject added to a curriculum or offered in a term. The old system worked these out in the
 * browser and saved what was posted; here the server always computes them (amounts in config ciss.scheduling.fees):
 *  - lecture fee: graduate school subjects = typed by the user, NSTP = fixed amount, others = units x rate per unit
 *  - laboratory fee: subjects with laboratory units, or thesis / practicum
 *  - development fee: OJT
 *  - fund account: the user's pick among config ciss.scheduling.fund_accounts (suggested: laboratory / NSTP)
 */
class SubjectFees
{
    public const NO = 'No';
    public const OJT = 'Yes';
    public const THESIS = 'YesThesis';
    public const PRACTICUM = 'YesPrac';

    // OJT / Thesis choices: practicum (and thesis, curricula only) in Summer
    public static function ojtOptions(int $semester, bool $withThesis = false): array
    {
        $options = [self::NO => 'No'];

        if ($semester === 3) {
            if ($withThesis) {
                $options[self::THESIS] = "Yes, it's Thesis";
            }
            $options[self::PRACTICUM] = "Yes, it's Practicum";
        }

        return $options + [self::OJT => "Yes, it's OJT"];
    }

    public static function isGraduate(string $subjectCode): bool
    {
        return str_starts_with($subjectCode, config('ciss.scheduling.graduate_subject_prefix'));
    }

    public static function isNstp(string $subjectCode): bool
    {
        return in_array($subjectCode, config('ciss.report_card.nstp_subjects'), true);
    }

    /**
     * lecUnit, labUnit, subUnit, lecFee, labFee, devFee of $subject; $graduateLectureFee is only used for
     * graduate school subjects.
     */
    public static function compute(Subject $subject, string $ojt, $graduateLectureFee = null): array
    {
        $fees = config('ciss.scheduling.fees');
        $lecUnits = (int) $subject->sublecredit;
        $labUnits = (int) $subject->sublabcredit;
        $units = $lecUnits + $labUnits;

        $lecFee = match (true) {
            self::isGraduate($subject->sub_code) => max(0, (float) $graduateLectureFee),
            self::isNstp($subject->sub_code) => $fees['nstp_lecture'],
            default => $units * $fees['per_unit'],
        };

        $labFee = match (true) {
            $labUnits > 0 => $fees['laboratory'],
            in_array($ojt, [self::THESIS, self::PRACTICUM], true) => $fees['thesis_laboratory'],
            default => 0,
        };

        return [
            'lecUnit' => $lecUnits,
            'labUnit' => $labUnits,
            'subUnit' => $units,
            'lecFee' => $lecFee,
            'labFee' => $labFee,
            'devFee' => $ojt === self::OJT ? $fees['ojt_development'] : 0,
        ];
    }

    // The fund account suggested for $subject (accountappraisal.id) or null
    public static function suggestedFund(Subject $subject): ?int
    {
        $accounts = config('ciss.scheduling.fund_accounts');

        return match (true) {
            (int) $subject->sublabcredit > 0 => $accounts['laboratory'],
            self::isNstp($subject->sub_code) => $accounts['nstp'],
            default => null,
        };
    }

    // [accountappraisal.id => "164 - LAB FEE"] for the Fund selects
    public static function fundOptions(): array
    {
        return self::fundAccounts()->mapWithKeys(fn ($account) => [$account->id => "{$account->fund_id} - {$account->account_name}"])->all();
    }

    // ['fund' => fund_id, 'fundAccount' => account_name] of a picked account id; both null for "No Account"
    public static function fundColumns(?int $accountId): array
    {
        $account = $accountId ? self::fundAccounts()->get($accountId) : null;

        return ['fund' => $account?->fund_id, 'fundAccount' => $account?->account_name];
    }

    private static $fundAccounts = null;

    // Read once per request
    private static function fundAccounts()
    {
        return self::$fundAccounts ??= AccountAppraisal::whereIn('id', array_values(config('ciss.scheduling.fund_accounts')))
            ->get(['id', 'fund_id', 'account_name'])
            ->keyBy('id');
    }
}
