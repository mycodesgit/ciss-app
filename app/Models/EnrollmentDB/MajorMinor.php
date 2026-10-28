<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MajorMinor extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'majorminor_subject';
    protected $primaryKey = 'id';

    protected $fillable = [
        'submamiID',
        'submamiName'
    ];
}
