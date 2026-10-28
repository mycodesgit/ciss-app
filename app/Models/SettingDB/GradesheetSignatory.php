<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradesheetSignatory extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'barangays';

    protected $fillable = [
        'schlyear',
        'semester',
        'campus',
        'namesign',
        'positionsign',
    ];
}
