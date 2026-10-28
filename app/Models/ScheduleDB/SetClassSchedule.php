<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetClassSchedule extends Model
{
    use HasFactory;
    protected $connection = 'schedule';
    protected $table = 'scheduleclass';

    protected $fillable = [
        'schedday',
        'start_time',
        'end_time',
        'progcodename',
        'progcodesection',
        'schlyear',
        'semester',
        'postedBy',
        'campus',
        'subject_id',
        'faculty_id',
        'room_id',
        'remarks',
        'is_merged',
        'merge_sections'
    ];
}
