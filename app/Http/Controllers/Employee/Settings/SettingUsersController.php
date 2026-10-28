<?php

namespace App\Http\Controllers\Employee\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\Settings\StoreUserRequest;
use App\Http\Requests\Employee\Settings\UpdateUserRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Models\AdmissionDB\User;
use App\Models\SettingDB\ButtonAccess;
use App\Models\SettingDB\ConfigureCurrent;

/**
 * Settings > User's Management. The page loads once; the table and every
 * action talk to the JSON endpoints below (public/js/employee/settings/users.js).
 */
class SettingUsersController extends Controller
{
    private const STATUS_DISABLED = 2;

    public function index()
    {
        return view('pages.employee.setting.user.listusers', [
            'roles' => config('ciss.roles'),
            'campuses' => config('ciss.campuses'),
            'departments' => config('ciss.departments'),
            'extensions' => StoreUserRequest::NAME_EXTENSIONS,
            'menuButtons' => $this->grantableMenus(),
            'schoolYears' => ConfigureCurrent::query()->distinct()->orderByDesc('schlyear')->pluck('schlyear'),
        ]);
    }

    // DataTables source. Only the fields the table and edit forms need (never the password hash).
    public function list()
    {
        $users = User::orderBy('lname')
            ->get(['id', 'fname', 'mname', 'lname', 'ext', 'email', 'campus', 'dept', 'role', 'statuser'])
            ->map(fn (User $user) => $user->only(['id', 'fname', 'mname', 'lname', 'ext', 'email', 'campus', 'dept', 'role', 'statuser']) + [
                'display_name' => $user->display_name,
                'role_name' => $user->role_name ?? 'Unknown Role',
                'is_active' => $user->isActive(),
            ]);

        return response()->json(['data' => $users]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['statuser'] = User::ACTIVE_STATUS;

        $user = User::create($data);
        ButtonAccess::firstOrCreate(['user_id' => $user->id]);

        return $this->ok('User stored successfully.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return $this->ok('User updated successfully.');
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return $this->ok('User password updated successfully.');
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'statuser' => ['required', Rule::in([User::ACTIVE_STATUS, self::STATUS_DISABLED])],
        ]);

        if ($user->is(Auth::guard('web')->user())) {
            return response()->json(['message' => 'You cannot change the status of your own account.'], 422);
        }

        $user->update(['statuser' => $validated['statuser']]);

        return $this->ok('User status updated successfully.');
    }

    public function showMenuAccess(User $user)
    {
        return response()->json(['buttons' => $user->buttonAccess?->buttons ?? []]);
    }

    public function updateMenuAccess(Request $request, User $user)
    {
        $validated = $request->validate([
            'buttons' => ['nullable', 'array'],
            'buttons.*' => [Rule::in(array_keys($this->grantableMenus()))],
        ]);

        ButtonAccess::updateOrCreate(['user_id' => $user->id], ['buttons' => $validated['buttons'] ?? []]);

        return $this->ok('User access updated successfully.');
    }

    public function showYearAccess(User $user)
    {
        return response()->json(['schlyraccess' => $user->buttonAccess?->schlyraccess ?? []]);
    }

    public function updateYearAccess(Request $request, User $user)
    {
        $validated = $request->validate([
            'schlyraccess' => ['nullable', 'array'],
            'schlyraccess.*' => ['string', 'max:20'],
        ]);

        ButtonAccess::updateOrCreate(['user_id' => $user->id], ['schlyraccess' => $validated['schlyraccess'] ?? []]);

        return $this->ok('Academic year access updated successfully.');
    }

    // Menu keys an administrator can grant (everything except items every employee always has)
    private function grantableMenus(): array
    {
        return collect(config('ciss.employee_menu'))
            ->reject(fn ($item) => $item['always'] ?? false)
            ->map(fn ($item) => $item['label'])
            ->all();
    }

    private function ok(string $message)
    {
        return response()->json(['success' => true, 'message' => $message]);
    }
}
