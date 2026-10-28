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
}
