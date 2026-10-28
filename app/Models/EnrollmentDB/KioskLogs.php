<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KioskLogs extends Model
{
    use HasFactory;

    protected $connection = 'enrollment';
    protected $table = 'kiosklogs';


    protected $fillable = [
        'studidres',
        'postedBy',
    ];
}
