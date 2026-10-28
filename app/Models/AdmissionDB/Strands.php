<?php

namespace App\Models\AdmissionDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Strands extends Model
{
    use HasFactory;

    protected $table = 'ad_strand';

    protected $fillable = [
        'campus', 
        'code', 
        'strand', 
    ];
}
