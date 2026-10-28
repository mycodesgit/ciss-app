<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudResetPass extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'studpassreset';

    protected $fillable = [
        'studid',
        'email',
        'fname',
        'lname',
    ];
}
