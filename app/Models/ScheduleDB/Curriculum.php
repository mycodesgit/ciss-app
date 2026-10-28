<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    use HasFactory;

    protected $connection = 'schedule';
    protected $table = 'curriculum';

    protected $fillable = [
        'progCode',
        'yrlvl',
        'subSec',
        'semester',
        'campus',
        'subCode',
        'lecUnit',
        'labUnit',
        'subUnit',
        'lecFee',
        'labFee',
        'devFee',
        'isOJT',
        'isTemp',
        'fund',
        'fundAccount',
        'itfee',
        'isType',
        'postedBy',
        'prerequisite',
    ];
}
