<?php

namespace App\Models\AdmissionDB;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campus',
        'dept',
        'lname',
        'fname',
        'mname',
        'ext',
        'email',
        'password',
        'role',
        'statuser',
        'remember_token'
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public const ADMIN_ROLE = 0;
    public const ACTIVE_STATUS = 1; // statuser: 1 = active, anything else = disabled

    public function getIsAdminAttribute($value)
    {
        return (bool) $value;
    }
    public function hasRole($role)
    {
        return $this->role === $role;
    }
    public function scopeActive($query)
    {
        return $query->where('statuser', self::ACTIVE_STATUS);
    }
    public function scopeInactive($query)
    {
        return $query->where(fn ($q) => $q->where('statuser', '!=', self::ACTIVE_STATUS)->orWhereNull('statuser'));
    }
    public function isActive(): bool
    {
        return (int) $this->statuser === self::ACTIVE_STATUS;
    }
    public function isAdministrator(): bool
    {
        return is_numeric($this->role) && (int) $this->role === self::ADMIN_ROLE;
    }
    public function getFullNameAttribute(): string
    {
        return trim($this->fname . ' ' . $this->lname);
    }
    // e.g. "Juan D. Dela Cruz Jr."
    public function getDisplayNameAttribute(): string
    {
        $middleInitial = $this->mname ? mb_substr($this->mname, 0, 1) . '.' : null;

        return collect([$this->fname, $middleInitial, $this->lname, $this->ext])->filter()->implode(' ');
    }
    public function getRoleNameAttribute(): ?string
    {
        return is_numeric($this->role) ? config('ciss.roles')[(int) $this->role] ?? null : null;
    }
    public function getCampusNameAttribute(): ?string
    {
        return config('ciss.campuses')[$this->campus] ?? $this->campus;
    }
    public function buttonAccess(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\SettingDB\ButtonAccess', 'user_id');
    }
    public function hasPermissionToAccess($button)
    {
        return in_array($button, $this->buttonAccess?->buttons ?? []);
    }
    // Administrators can open every menu; everyone else needs it in button_access
    public function canAccessMenu($button): bool
    {
        return $this->isAdministrator() || $this->hasPermissionToAccess($button);
    }
}
