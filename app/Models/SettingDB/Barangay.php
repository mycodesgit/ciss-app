<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;
    protected $connection = 'settings';
    protected $table = 'barangays';

    protected $fillable = [
        'code',
        'name',
        'region_id',
        'province_id',
        'city_id',
    ];
}
