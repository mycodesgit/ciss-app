<?php

namespace App\Models\AdmissionDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamineeResult extends Model
{
    use HasFactory;

    protected $table = 'ad_examinee_result';

    protected $fillable = [
        'app_id',
        'camp',
        'admission_id',
        'raw_score', 
        'percentile', 
        'qualifier',
        'rawscoredate',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'rawscoredate' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Stanine (1-9) of a raw score, using config('ciss.stanines'); null when not a valid score
    public static function stanineFor($rawScore): ?int
    {
        if (!is_numeric($rawScore) || (int) $rawScore < 1) {
            return null;
        }

        foreach (config('ciss.stanines') as $maxScore => $stanine) {
            if ((int) $rawScore <= $maxScore) {
                return $stanine;
            }
        }

        return null;
    }

    public const REMARK_FAILED = 'Failed';
    public const SECOND_QUALIFIER = 2; // qualifier: a failed examinee allowed to continue

    // Remark of a raw score for a campus: by passing score (e.g. Main: Qualified/Failed) or by stanine (Passed/Failed)
    public static function remarkFor($rawScore, ?string $campus): ?string
    {
        $passingScore = config('ciss.passing_scores')[$campus] ?? null;

        if ($passingScore !== null) {
            return is_numeric($rawScore) ? ((int) $rawScore >= $passingScore ? 'Qualified' : self::REMARK_FAILED) : null;
        }

        $stanine = self::stanineFor($rawScore);

        return $stanine ? config('ciss.stanine_remarks')[$stanine] ?? null : null;
    }

    // Save an applicant's raw score with the computed remark (qualifier only counts for failed results)
    public static function record(Applicant $applicant, int $rawScore, bool $secondQualifier = false): self
    {
        $remark = self::remarkFor($rawScore, $applicant->campus);

        return self::updateOrCreate(
            ['app_id' => $applicant->id],
            [
                'camp' => $applicant->campus,
                'admission_id' => $applicant->admission_id,
                'raw_score' => $rawScore,
                'percentile' => $remark,
                'qualifier' => $remark === self::REMARK_FAILED && $secondQualifier ? self::SECOND_QUALIFIER : null,
                'rawscoredate' => now(),
            ]
        );
    }

    // Can go on to the Confirm list: passed/qualified, or a failed second qualifier
    public function canProceed(): bool
    {
        return is_numeric($this->raw_score)
            && ($this->percentile !== self::REMARK_FAILED || (int) $this->qualifier === self::SECOND_QUALIFIER);
    }

    // Remark shown on reports: only "Passed" or "Failed" (a campus's "Qualified" shows as "Passed")
    public function displayRemark(): string
    {
        return $this->percentile === self::REMARK_FAILED ? 'Failed' : 'Passed';
    }

    public function user()
    {
        return $this->belongsToMany('App\Models\AdmissionDB\User', 'admission_id');
    }
}
