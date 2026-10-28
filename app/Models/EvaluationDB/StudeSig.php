<?php

namespace App\Models\EvaluationDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudeSig extends Model
{
    use HasFactory;

    protected $connection = 'evaluation';
    protected $table = 'studsignature';

    protected $fillable = [
        'studIDno',
        'camp',
        'studesig',
        'status'
    ];
}
