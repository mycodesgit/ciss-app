<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $connection = 'schedule';
    protected $table = 'rooms';

    protected $fillable = [
        'room_name', 
        'college_room', 
        'room_capacity',
        'campus', 
        'status',
    ];

    // rooms.status (enum '1' / '2')
    public const ACTIVE = '1';
    public const INACTIVE = '2';
    public const STATUSES = [self::ACTIVE => 'Active', self::INACTIVE => 'Inactive'];
}
