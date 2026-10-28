<?php

namespace App\Models\EvaluationDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QCEsetting extends Model
{
    use HasFactory;

    protected $connection = 'evaluation';
    protected $table = 'qcesetting';

    protected $fillable = [
        'statuseval', 
    ];
}
