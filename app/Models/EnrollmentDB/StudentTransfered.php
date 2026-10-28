<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTransfered extends Model
{
    use HasFactory;

    protected $connection = 'enrollment';
    protected $table = 'studenttransfer';

    protected $fillable = [
        'stud_id',
        'studbaseprim_id',
        'fromcampus',
        'tocampus',
        'transferby',
    ];
}
