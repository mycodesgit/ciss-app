<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentMode extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'enrollmentmode';

    protected $fillable = [
        'statusenroll',
        'type',
        'campus', 
        'postedBy', 
    ];
}
