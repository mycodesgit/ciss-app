<?php

namespace App\Models\AssessmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Fee template per program / year level / term / campus (set by the Assessment Office)
class StudentFee extends Model
{
    use HasFactory;

    protected $connection = 'assessment';
    protected $table = 'student_fee';

    protected $fillable = [
        'prog_Code',
        'yrlevel',
        'schlyear',
        'semester',
        'campus',
        'fundname_code',
        'amountFee',
        'accountName',
        'postedBy',
        'remember_token',
    ];
}
