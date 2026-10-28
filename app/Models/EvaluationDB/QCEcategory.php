<?php

namespace App\Models\EvaluationDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QCEcategory extends Model
{
    use HasFactory;

    protected $connection = 'evaluation';
    protected $table = 'qcecategory';

    protected $fillable = [
        'catName', 
        'catDesc', 
        'catstatus',
        'postedBy',
    ];
}
