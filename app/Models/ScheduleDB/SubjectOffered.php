<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectOffered extends Model
{
    use HasFactory;
    protected $connection = 'schedule';
    protected $table = 'sub_offered';

    protected $fillable = [
        'subCode',
        'subSec', 
        'schlyear', 
        'semester',
        'campus',
        'lecFee',
        'labFee',
        'devFee',
        'subUnit',
        'postedBy',
        'datePosted',
        'lecUnit',
        'labUnit',
        'maxstud',
        'isOJT',
        'isTemp',
        'isType',
        'fund',
        'itfee',
        'fundAccount',
    ];

    // Relationship to Subject
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subCode', 'sub_code');
    }

    // Relationship to Program through subSec
    public function program()
    {
        return $this->belongsTo(Program::class, 'subSec', 'progAcronym');
    }
}
