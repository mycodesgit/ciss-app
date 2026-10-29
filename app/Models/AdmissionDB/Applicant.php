<?php

namespace App\Models\AdmissionDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    protected $table = 'ad_applicant_admission';

    // p_status: the applicant's step in the admission process
    public const STATUS_REGISTERED = 1;   // registered, no exam schedule yet
    public const STATUS_SCHEDULED = 2;    // exam scheduled (examinee)
    public const STATUS_WITH_RESULT = 3;  // exam score encoded
    public const STATUS_CONFIRMED = 4;    // confirmed, ready for department interview
    public const STATUS_ACCEPTED = 5;     // accepted after the department interview
    public const STATUS_ENROLLMENT = 6;   // pushed to enrollment (student record created)
    public const STATUS_CANCELLED = 7;    // removed from the applicant list

    // Every step from registered to pushed to enrollment (all but cancelled), used by the reports
    public const ACTIVE_STATUSES = [
        self::STATUS_REGISTERED,
        self::STATUS_SCHEDULED,
        self::STATUS_WITH_RESULT,
        self::STATUS_CONFIRMED,
        self::STATUS_ACCEPTED,
        self::STATUS_ENROLLMENT,
    ];

    public const TYPES = [
        1 => 'New',
        2 => 'Returnee',
        3 => 'Transferee',
    ];

    // status: re-upload state of the applicant's documents
    public const REUPLOAD_STATUSES = [
        1 => 'No Need to Reupload',
        2 => 'Reupload',
        3 => 'Done Reupload',
    ];

    // Documents an applicant can be asked to upload again (ad_reupload.reuploadallow values)
    public const REUPLOAD_DOCUMENTS = [
        'studiddoc_image' => 'School ID / Valid ID',
        'proofdoc_image' => 'Proof of Equity Target Document',
    ];

    public function documents()
    {
        return $this->hasOne(ApplicantDocs::class, 'app_id');
    }

    // Department interview result (joined by applicant id; admission_id can repeat across campuses)
    public function deptRating()
    {
        return $this->hasOne(DeptRating::class, 'app_id');
    }

    // Enrollment record created when the accepted applicant is pushed to enrollment
    public function student()
    {
        return $this->hasOne(\App\Models\EnrollmentDB\Student::class, 'app_id');
    }

    public function reupload()
    {
        return $this->hasOne(AdReupload::class, 'appid');
    }

    // e.g. "Juan D. Dela Cruz Jr."
    public function getDisplayNameAttribute(): string
    {
        $middleInitial = $this->mname ? mb_substr($this->mname, 0, 1) . '.' : null;
        $ext = $this->ext && $this->ext !== 'N/A' ? $this->ext : null;

        return collect([$this->fname, $middleInitial, $this->lname, $ext])->filter()->implode(' ');
    }

    public function hasExamSchedule(): bool
    {
        return !empty($this->d_admission) && !empty($this->time) && $this->time !== '00:00:00' && !empty($this->venue);
    }

    protected $fillable = [
        'studagree',
        'year',
        'en_status',
        'admission_id',
        'status',
        'p_status',
        'campus',
        'type',
        'fname', 
        'lname', 
        'mname',
        'ext',
        'gender',
        'bday',
        'age',
        'contact',
        'email',
        'civil_status',
        'religion',
        'region',
        'monthly_income',
        'hnum',
        'brgy',
        'city',
        'province',
        'region',
        'zcode',
        'address',
        'mdc',
        'lstsch_attended',
        'sch_yr',
        'award',
        'course',
        'preference_1',
        'preference_2',
        'preference_3',
        'dateID',
        'd_admission',
        'time',
        'venue',
        'created_at'
    ];

    public function result()
    {
        return $this->hasOne('App\Models\AdmissionDB\ExamineeResult', 'app_id','id');
    }

    public function interview()
    {
        return $this->hasOne('App\Models\AdmissionDB\DeptRating', 'admission_id','admission_id');
    }
}
