<?php

namespace App\Models\AssessmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funds extends Model
{
    use HasFactory;
    protected $connection = 'assessment';
    protected $table = 'funds';

    protected $fillable = [
        'prog_id',
        'fund_name',
        'remember_token', 
    ];
}
