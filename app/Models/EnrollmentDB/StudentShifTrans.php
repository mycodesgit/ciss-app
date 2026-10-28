<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentShifTrans extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'student_shiftrans';

    protected $fillable = [
        'studentShiftTransDesc',
    ];
}
