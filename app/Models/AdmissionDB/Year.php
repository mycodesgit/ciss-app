<?php

namespace App\Models\AdmissionDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    use HasFactory;
    protected $table = 'admission_year';

    protected $fillable = [
        'adyear',
        'status',
    ];

    // The admission year currently switched "On"
    public static function current()
    {
        return static::where('status', 'On')->value('adyear');
    }
}
