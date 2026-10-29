<?php

namespace App\Models\ScholarshipDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholar extends Model
{
    use HasFactory;

    protected $connection = 'scholarship';
    protected $table = 'scholarship';

    protected $fillable = [
        'scholar_name',
        'scholar_sponsor',
        'chedcategory',
        'unicategory',
        'fund_source',
    ];
}
