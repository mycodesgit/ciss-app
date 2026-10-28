<?php

namespace App\Models\AdmissionDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamineeResult extends Model
{
    use HasFactory;

    protected $table = 'ad_examinee_result';

    protected $fillable = [
        'app_id',
        'camp',
        'admission_id',
        'raw_score', 
        'percentile', 
        'qualifier',
        'rawscoredate',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'rawscoredate' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsToMany('App\Models\AdmissionDB\User', 'admission_id');
    }
}
