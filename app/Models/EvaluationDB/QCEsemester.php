<?php

namespace App\Models\EvaluationDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QCEsemester extends Model
{
    use HasFactory;

    protected $connection = 'evaluation';
    protected $table = 'qceschlyearsem';

    protected $fillable = [
        'qceschlyear', 
        'qcesemester',
        'qceratingfrom',
        'qceratingto',
        'qcesemstat',
        'postedBy',
    ];
}
