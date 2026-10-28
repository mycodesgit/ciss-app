<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectAcademicType extends Model
{
    use HasFactory;
    
    protected $connection = 'schedule';
    protected $table = 'subjectacadtype';

    protected $fillable = [
        'acadtype_name', 
        'remember_token',
    ];
}
