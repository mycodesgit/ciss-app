<?php

namespace App\Models\AdmissionDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeptRating extends Model
{
    use HasFactory;

    protected $table = 'ad_applicant_dept_rating';

    protected $fillable = [
        'app_id',
        'interviewerid',
        'camp',
        'admission_id', 
        'interviewer',
        'rating', 
        'remarks',
        'course',
        'deptcol',
        'reason',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    // remarks of the department interview
    public const REMARKS = [
        1 => 'Interview Completed',
        2 => 'Interview Pending',
    ];

    public const REMARK_COMPLETED = 1;

    public function isCompleted(): bool
    {
        return (int) $this->remarks === self::REMARK_COMPLETED;
    }

    // The Admission dashboard counts applicants that have a rating row: a new / changed / deleted row refreshes it
    protected static function booted(): void
    {
        $forget = fn (DeptRating $rating) => Applicant::find($rating->app_id)?->forgetDashboardCache();

        static::saved($forget);
        static::deleted($forget);
    }
}
