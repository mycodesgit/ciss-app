<?php

namespace App\Models\SettingDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * The grades password of a campus (Accounts > Grades Password), asked by the Gradesheet to edit a submitted grade.
 * Saved hashed ('hashed' cast). Passwords saved as plain text before (or by coas-app) are still accepted until
 * they are changed here; isProtected() tells which.
 */
class GradePass extends Model
{
    use HasFactory;

    protected $connection = 'settings';
    protected $table = 'gradepass_conf';

    protected $fillable = [
        'gradeauthpass',
        'campus'
    ];

    protected $hidden = ['gradeauthpass'];

    protected $casts = [
        'gradeauthpass' => 'hashed',
    ];

    // Whether the typed password is the campus grades password (hashed, or an old plain one)
    public static function matches(?string $campus, string $password): bool
    {
        $stored = (string) static::where('campus', $campus)->value('gradeauthpass');

        if ($stored === '') {
            return false;
        }

        return self::isHash($stored) ? Hash::check($password, $stored) : hash_equals($stored, $password);
    }

    // Saved hashed (not readable); false = an old plain-text password, to be changed
    public function isProtected(): bool
    {
        return self::isHash((string) $this->getRawOriginal('gradeauthpass'));
    }

    private static function isHash(string $value): bool
    {
        return password_get_info($value)['algoName'] !== 'unknown';
    }
}
