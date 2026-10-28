<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatoriesEmp extends Model
{
    use HasFactory;
    protected $connection = 'settings';
    protected $table = 'signatoriesemp';

    protected $fillable = [
        'fulname',
        'titledeg',
        'position',
        'schlyear',
        'semester',
        'campus',
        'status',
        'esign',
    ];
}
