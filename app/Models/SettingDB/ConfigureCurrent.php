<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigureCurrent extends Model
{
    use HasFactory;
    protected $connection = 'settings';
    protected $table = 'settings_conf';

    public const SEMESTERS = [
        1 => '1st Semester',
        2 => '2nd Semester',
        3 => 'Summer',
    ];

    public const STATUSES = [
        1 => 'Inactive',
        2 => 'Active',
        3 => 'Upcoming',
        4 => 'Previous',
    ];

    protected $fillable = [
        'schlyear',
        'semester',
        'set_status',
    ];

    public const STATUS_ACTIVE = 2;

    // The school year & semester currently set to Active
    public static function active(): ?self
    {
        return static::where('set_status', self::STATUS_ACTIVE)->first();
    }

    // The term configured just before this one
    public function previous(): ?self
    {
        return static::where('id', '<', $this->id)->orderByDesc('id')->first();
    }

    // e.g. "1st Semester 2025-2026"
    public function getLabelAttribute(): string
    {
        return $this->semester_name . ' ' . $this->schlyear;
    }

    public function getSemesterNameAttribute(): string
    {
        return self::SEMESTERS[$this->semester] ?? (string) $this->semester;
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->set_status] ?? (string) $this->set_status;
    }
}
