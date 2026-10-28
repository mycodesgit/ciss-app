<?php

namespace App\Models\EvaluationDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QCEratingscale extends Model
{
    use HasFactory;

    protected $connection = 'evaluation';
    protected $table = 'qceratingscale';

    protected $fillable = [
        'inst_scale', 
        'inst_descRating',
        'inst_qualDescription',
        'instratingscalestat',
        'postedBy',
    ];
}
