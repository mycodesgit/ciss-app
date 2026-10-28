<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $connection = 'settings';
    protected $table = 'cities';

    protected $fillable = [
        'code',
        'name',
        'region_id',
        'province_id',
        'city_id',
        'zip_code',
    ];
}
