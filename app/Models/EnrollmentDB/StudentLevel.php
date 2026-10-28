<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLevel extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'studentlevel';

    protected $fillable = [
        'studLevel',
        'subletter'
    ];
}
