<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionMode extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'admissionmode';

    protected $fillable = [
        'statusadmission',
    ];
}
