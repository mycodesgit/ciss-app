<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentInfoGrad extends Model
{
    use HasFactory;

    protected $connection = 'enrollment';
    protected $table = 'studentsgradinfo';

    protected $fillable = [
        'studIDprim',
        'studIDno',
        'spouseparent',
        'elementary',
        'elemyeargrad',
        'highschool',
        'highschoolyeargrad',
        'tertiary',
        'tertiaryyeargrad',
        'tertiarycourse',
        'tertiarymajor',
        'masterspeciallization',
        'masterschool',
        'masternounit',
        'masteraddress',
        'masterinclyear',
        'doctorcourse',
        'doctorspecialization',
        'doctorschool',
        'doctornounit',
        'doctoraddress',
        'doctorinclyear',
    ];
}
