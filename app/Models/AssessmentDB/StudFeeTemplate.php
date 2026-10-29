<?php

namespace App\Models\AssessmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudFeeTemplate extends Model
{
    use HasFactory;

    protected $connection = 'assessment';
    protected $table = 'student_feetemplate';

    protected $fillable = [
        'temptype',
        'yrlevel',
        'semester',
        'fundname_code',
        'amountFee',
        'accountName',
        'postedBy',
        'remember_token', 
    ];
}
