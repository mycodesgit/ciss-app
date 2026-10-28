<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'regions';

    protected $fillable = [
        'code',
        'name',
        'region_id',
    ];
}
