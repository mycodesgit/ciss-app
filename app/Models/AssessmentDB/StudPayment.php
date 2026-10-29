<?php

namespace App\Models\AssessmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudPayment extends Model
{
    use HasFactory;
    protected $connection = 'assessment';
    protected $table = 'studpayment';

    protected $fillable = [
        'orno',
        'studID',
        'semester',
        'schlyear',
        'campus',
        'fund',
        'account',
        'datepaid',
        'amountpaid',
        'postedBy',
        'remember_token', 
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\EnrollmentDB\Student::class, 'studID', 'stud_id');
    }
}
