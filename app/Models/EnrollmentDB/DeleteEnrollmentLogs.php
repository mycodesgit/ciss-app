<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeleteEnrollmentLogs extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'delete_enrollment_logs';

    protected $fillable = [
        'delstudentID',
        'delMC',
        'delemployeename',
        'delsemester',
        'delschlyear',
    ];
}
