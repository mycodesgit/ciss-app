<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyLoad extends Model
{
    use HasFactory;

    protected $connection = 'schedule';
    protected $table = 'facultyload';

    protected $fillable = [
        'facultyID',
        'subjectID', 
        'remember_token', 
    ];
}
