<?php

namespace App\Models\AdmissionDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    use HasFactory;

    protected $table = 'ad_time';

    protected $fillable = [
        'campus', 
        'date', 
        'time', 
        'slots', 
    ];
}
