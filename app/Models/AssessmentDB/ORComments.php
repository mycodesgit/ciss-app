<?php

namespace App\Models\AssessmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ORComments extends Model
{
    use HasFactory;
    protected $connection = 'assessment';
    protected $table = 'orcomments';

    protected $fillable = [
        'studpayID',
        'orno',
        'studID',
        'semester',
        'schlyear',
        'campus',
        'datepaid',
        'comments',
        'postedBy',
    ];
}
