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
