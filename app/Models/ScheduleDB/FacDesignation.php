<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacDesignation extends Model
{
    use HasFactory;
    protected $connection = 'schedule';
    protected $table = 'fac_designation';

    protected $fillable = [
        'schlyear', 
        'semester', 
        'campus',
        'facCollege',
        'fac_id',
        'facname',
        'designation',
        'dunit', 
    ];
}
