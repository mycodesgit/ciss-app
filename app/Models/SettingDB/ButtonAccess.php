<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ButtonAccess extends Model
{
    use HasFactory;
    protected $connection = 'settings';
    protected $table = 'button_access';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'buttons', 
        'schlyraccess', 
    ];

    protected $casts = [
        'buttons' => 'array',
        'schlyraccess' => 'array',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\AdmissionDB\User', 'user_id');
    }
    public function facultyUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\ScheduleDB\Faculty', 'user_id');
    }
}
