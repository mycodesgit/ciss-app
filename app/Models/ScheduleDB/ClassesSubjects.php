<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassesSubjects extends Model
{
    use HasFactory;
    protected $connection = 'schedule';
    protected $table = 'classes_subject';

    protected $fillable = [
        'classEnroll_ID',
        'classSubject_ID'
    ];
}
