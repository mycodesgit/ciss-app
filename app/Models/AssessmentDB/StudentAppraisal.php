<?php

namespace App\Models\AssessmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// A student's assessed fee lines of a term (saved when the student is enrolled)
class StudentAppraisal extends Model
{
    use HasFactory;

    protected $connection = 'assessment';
    protected $table = 'student_appraisal';

    protected $fillable = [
        'studID',
        'semester',
        'schlyear',
        'campus',
        'fundID',
        'account',
        'dateAssess',
        'amount',
        'postedBy',
    ];
}
