<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigureCurrent extends Model
{
    use HasFactory;
    protected $connection = 'settings';
    protected $table = 'settings_conf';

    protected $fillable = [
        'schlyear',
        'semester',
        'set_status', 
    ];
}
