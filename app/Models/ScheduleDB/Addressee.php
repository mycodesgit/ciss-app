<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addressee extends Model
{
    use HasFactory;

    protected $connection = 'schedule';
    protected $table = 'addressee';

    protected $fillable = [
        'adrDesc', 
    ];
}
