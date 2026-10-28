<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncodedGrade extends Model
{
    use HasFactory;

    protected $connection = 'enrollment';
    protected $table = 'studgrades_logs';

    protected $fillable = [
        'grdeprimID',
        'studsID',
        'subjctsID',
        'datefgrade',
        'datecgrade',
        'campus',
        'fgrade',
        'cgrade',
        'encodedBy',
    ];
}
