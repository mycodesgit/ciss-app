<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGnderStatus extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'studgenderstat';

    protected $fillable = [
        'genderstat_name',
    ];
}
