<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $connection = 'schedule';
    protected $table = 'department';

    protected $fillable = [
        'deptCod', 
        'deptName', 
        'collegeCod'
    ];
}
