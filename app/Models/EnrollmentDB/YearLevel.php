<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearLevel extends Model
{
    use HasFactory;
    protected $connection = 'enrollment';
    protected $table = 'yearlevel';

    protected $fillable = [
        'yearleveldesc',
        'yearsection'
    ];
}
