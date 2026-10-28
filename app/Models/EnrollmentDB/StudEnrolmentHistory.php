<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudEnrolmentHistory extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'program_en_history';

    protected $fillable = [
        'studentID',
        'schlyear',
        'semester',
        'campus',
        'course',
        'progCod',
        'studMajor',
        'studMinor',
        'studLevel',
        'studYear',
        'studSec',
        'studUnit',
        'studStatus',
        'studSch',
        'studClassID',
        'postedBy',
        'confirmBy',
        'postedDate',
        'studType',
        'transferee',
        'fourPs',
        'status'
    ];
}
