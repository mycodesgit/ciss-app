<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'campus';
    protected $primaryKey = 'id';

    protected $fillable = [
        'code',
        'name',
        'login_enabled',
    ];
}
