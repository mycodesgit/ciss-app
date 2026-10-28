<?php

namespace App\Models\EvaluationDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QCEinstruction extends Model
{
    use HasFactory;

    protected $connection = 'evaluation';
    protected $table = 'qceinstruction';

    protected $fillable = [
        'instruction', 
        'instructcat',
        'postedBy',
    ];
}
