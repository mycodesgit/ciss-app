<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradesheetLogbook extends Model
{
    use HasFactory;

    protected $connection = 'enrollment';
    protected $table = 'gradesheetlogbook';

    protected $fillable = [
        'schlyear',
        'semester',
        'campus',
        'collegeabbr',
        'facultyid',
        'subjectid',
        'timebeingsubmitted',
    ];
}
