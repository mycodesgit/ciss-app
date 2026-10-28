<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnPrograms extends Model
{
    use HasFactory;

    protected $connection = 'schedule';
    protected $table = 'programs';

    protected $fillable = [
        'campus',
        'progCod',
        'progName',
        'progLev',
        'progSta',
        'proglad',
        'progDisc',
        'progLic',
        'progAut',
        'progYer',
        'progRev',
        'progAcr',
        'progYr1',
        'progYr2',
        'progNor',
        'progUni',
        'progDep',
        'progCollege',
        'progAcronym',
        'progFund',
        'progAccount',
    ];
    // Relationship to SubjectOffered
    public function subjectOffered()
    {
        return $this->hasMany(SubjectOffered::class, 'subSec', 'progAcronym');
    }
}
