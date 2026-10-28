<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRFID extends Model
{
    use HasFactory;

    protected $connection = 'enrollment';
    protected $table = 'studentrfidcard';

    protected $fillable = [
        'stdntid',
        'stdntrfid',
        'studphoto',
        'contactperson',
        'contactpersonno',
        'studsignature',
        'campus',
        'schlyear',
        'semester',
        'postedBy',
    ];
}
