<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradePass extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'gradepass_conf';

    protected $fillable = [
        'gradeauthpass', 
        'campus'
    ];
}
