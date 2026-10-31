<?php

namespace App\Services\Kiosk;

use Illuminate\Support\Facades\Hash;

use App\Models\AdmissionDB\User;
use App\Models\EnrollmentDB\KioskLogs;
use App\Models\EnrollmentDB\KioskUser;

/**
 * Student kiosk accounts (enrollment.kioskstudent), used by the Kiosk module.
 *  - passwords look like the old system's: 4 digits + K, U or G (e.g. "0427K"), generated on the server
 *  - only the hash is saved; a password is shown once, when it is set (Kiosk User modal, Bulk Generation PDF)
 *  - resetnumber counts how many times an account's password was reset
 */
class KioskAccounts
{
    public const ROLE = 'Student';
    private const PASSWORD_LETTERS = ['K', 'U', 'G'];

    public static function generatePassword(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT) . self::PASSWORD_LETTERS[random_int(0, 2)];
    }

    // Creates the account, or resets its password when it exists. $log: count it in the employee's kiosk logs
    public static function setPassword(string $studentId, string $password, User $by, bool $log = true): KioskUser
    {
        $account = KioskUser::firstOrNew(['studid' => $studentId]);
        $isReset = $account->exists;

        $account->fill([
            'password' => Hash::make($password),
            'postedBy' => $by->id,
            'role' => $account->role ?: self::ROLE,
            'resetnumber' => $isReset ? (int) $account->resetnumber + 1 : 0,
        ])->save();

        if ($log) {
            KioskLogs::create(['studidres' => $studentId, 'postedBy' => $by->id]);
        }

        return $account;
    }
}
