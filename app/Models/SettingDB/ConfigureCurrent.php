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

    public function getSemesterNameAttribute(): string
    {
        return self::SEMESTERS[$this->semester] ?? (string) $this->semester;
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->set_status] ?? (string) $this->set_status;
    }
}
