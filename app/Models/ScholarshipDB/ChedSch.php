<?php

namespace App\Models\ScholarshipDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChedSch extends Model
{
    use HasFactory;
    protected $connection = 'scholarship';
    protected $table = 'chedscholarship';

    protected $fillable = [
        'chedsch_name',
        
    ];
}
