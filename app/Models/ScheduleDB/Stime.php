<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stime extends Model
{
    use HasFactory;
    protected $connection = 'schedule';
    protected $table = 'stime';

    protected $fillable = [
        'timeDesc', 
    ];
}
