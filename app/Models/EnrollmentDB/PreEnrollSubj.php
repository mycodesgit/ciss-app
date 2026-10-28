<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreEnrollSubj extends Model
{
    use HasFactory;

    protected $connection = 'enrollment';
    protected $table = 'preenrollsubj';

    protected $fillable = [
        'studID',
        'subjID',
        'subjFgrade',
        'subjComp',
        'creditEarned',
        'status',
        'compstat',
        'postedBy',
        'campus',
        'encode'
    ];
}
