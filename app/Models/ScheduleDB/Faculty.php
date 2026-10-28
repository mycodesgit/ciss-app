<?php

namespace App\Models\ScheduleDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // Importing the Authenticatable trait
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Faculty extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $connection = 'schedule';
    protected $table = 'faculty';

    protected $fillable = [
        'campus',
        'campactive',
        'faccollege',
        'facdept',
        'deptmajor', 
        'fname', 
        'mname',
        'lname', 
        'ext', 
        'email',
        'password', 
        'role', 
        'rank',
        'prefix', 
        'suffix', 
        'verification_code',
        'status',
        'remember_token', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'role' => 'string',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function buttonAccess(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\SettingDB\ButtonAccess', 'user_id');
    }
    public function hasPermissionToAccess($button)
    {
        $buttonAccess = $this->buttonAccess;
        $buttons = $buttonAccess ? $buttonAccess->buttons : [];
        return in_array($button, $buttons);
    }
}
