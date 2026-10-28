<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $connection = 'schedule';
    protected $table = 'subjects';

    protected $fillable = [
        'sub_code',
        'subjcostcenter',
        'sub_name', 
        'sub_title',
        'subjcollege', 
        'subjdep',
        'sublecredit',
        'sublabcredit',
        'sub_unit',
        'subjweeks',
        'subjconthrs',
        'subjlev',
        'subdelmod',
        'subjprereq',
        'subacadtype'
    ];

    // Relationship to SubjectOffered
    public function subjectOffered()
    {
        return $this->hasMany(SubjectOffered::class, 'subCode', 'sub_code');
    }
}
