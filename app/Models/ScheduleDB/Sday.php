<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sday extends Model
{
    use HasFactory;
    protected $connection = 'schedule';
    protected $table = 'sday';

    protected $fillable = [
        'dayDesc', 
    ];
}
