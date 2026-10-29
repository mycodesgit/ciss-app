<?php

namespace App\Models\AdmissionDB;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
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

    // Cache key of the Settings dashboard user counts (per day, for "added today")
    public static function dashboardCountsCacheKey(): string
    {
        return 'settings_dashboard_user_counts_' . today()->toDateString();
    }

    // The counts are cleared whenever a user is added, changed or deleted
    protected static function booted(): void
    {
        $forget = fn () => Cache::forget(self::dashboardCountsCacheKey());

        static::saved($forget);
        static::deleted($forget);
    }

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
    // Administrator (0) and Registrar (3), every campus: Correction of Grades (change any grade, even submitted / INC).
    // Registrar Staff (4) is not included.
    public const GRADE_CORRECTION_ROLES = [0, 3];

    public function canCorrectGrades(): bool
    {
        return in_array((int) $this->role, self::GRADE_CORRECTION_ROLES, true);
    }

    // Administrator (0), Guidance Officer (1), Guidance Staff (2) and Registrar (3): Enrollment > Student Information.
    // Registrar Staff (4) is not included.
    public const STUDENT_INFO_ROLES = [0, 1, 2, 3];

    public function canManageStudentInfo(): bool
    {
        return in_array((int) $this->role, self::STUDENT_INFO_ROLES, true);
    }

    // Enrollment reports (Print Student RF, Student Per Degree): everyone but the Encoder (19)
    public const ENCODER_ROLE = 19;

    public function canViewEnrollmentReports(): bool
    {
        return (int) $this->role !== self::ENCODER_ROLE;
    }

    // Guidance Officer (1) and Guidance Staff (2) can view interview results but not record them
    public function canRecordInterviews(): bool
    {
        return !in_array((int) $this->role, [1, 2], true);
    }
    // Administrators and the Main Campus Guidance Officer can look at every campus's applicants
    public function canViewAllCampuses(): bool
    {
        return $this->isAdministrator() || ($this->campus === 'MC' && (int) $this->role === 1);
    }
    // Administrators can open every menu; everyone else needs it in button_access
    public function canAccessMenu($button): bool
    {
        return $this->isAdministrator() || $this->hasPermissionToAccess($button);
    }
}
