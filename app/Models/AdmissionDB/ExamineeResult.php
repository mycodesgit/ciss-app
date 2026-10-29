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

    // Stanine (1-9) of a raw score, using config('ciss.stanines'); null when not a valid score
    public static function stanineFor($rawScore): ?int
    {
        if (!is_numeric($rawScore) || (int) $rawScore < 1) {
            return null;
        }

        foreach (config('ciss.stanines') as $maxScore => $stanine) {
            if ((int) $rawScore <= $maxScore) {
                return $stanine;
            }
        }

        return null;
    }

    public static function remarkFor(?int $stanine): ?string
    {
        return $stanine ? config('ciss.stanine_remarks')[$stanine] ?? null : null;
    }

    public function user()
    {
        return $this->belongsToMany('App\Models\AdmissionDB\User', 'admission_id');
    }
}
