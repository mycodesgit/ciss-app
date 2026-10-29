<?php

namespace App\Models\ScholarshipDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FSCode extends Model
{
    use HasFactory;
    protected $connection = 'scholarship';
    protected $table = 'fscode';

    protected $fillable = [
        'chedsch_name',
        
    ];
}
