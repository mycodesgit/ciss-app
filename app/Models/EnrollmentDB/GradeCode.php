<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeCode extends Model
{
    use HasFactory;

    protected $connection = 'enrollment';
    protected $table = 'gradecode';

    protected $fillable = [
        'grade',
        'gradedesc',
    ];
}
