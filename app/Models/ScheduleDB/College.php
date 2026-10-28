<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    use HasFactory;
    protected $connection = 'schedule';
    protected $table = 'college';

    protected $fillable = [
        'college_abbr', 
        'college_name', 
        'campus', 
        'colcolor'
    ];
}
