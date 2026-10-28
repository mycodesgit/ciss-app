<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $connection = 'enrollment';
    protected $table = 'studgrades';

    protected $fillable = [
        'studID',
        'subjID',
        'subjFgrade',
        'subjComp',
        'creditEarned',
        'status',
        'compstat',
        'postedBy',
        'schlyear',
        'semester',
        'campus'
    ];
}
