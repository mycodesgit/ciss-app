<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCvlStatus extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'studcivilstat';

    protected $fillable = [
        'cvlstat_name',
    ];
}
