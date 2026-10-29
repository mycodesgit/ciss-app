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
        'subacadtype',
        'subjstatus',
    ];

    // subjstatus: subjects are never deleted, only made Inactive (offerings and grades point to them)
    public const ACTIVE = 1;
    public const INACTIVE = 0;

    // Relationship to SubjectOffered
    public function subjectOffered()
    {
        return $this->hasMany(SubjectOffered::class, 'subCode', 'sub_code');
    }
}
