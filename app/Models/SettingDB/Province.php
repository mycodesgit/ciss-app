<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;
    protected $connection = 'settings';
    protected $table = 'provinces';

    protected $fillable = [
        'code',
        'name',
        'region_id',
        'province_id',
    ];
}
