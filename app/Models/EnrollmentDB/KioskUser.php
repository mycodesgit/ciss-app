<?php

namespace App\Models\EnrollmentDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // Importing the Authenticatable trait
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class KioskUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $connection = 'enrollment';
    protected $table = 'kioskstudent';


    protected $fillable = [
        'studid',
        'password', 
        'passtext', 
        'postedBy',
        'role',  
        'resetnumber'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    // protected $casts = [
    //     'role' => 'string',
    // ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }
}
