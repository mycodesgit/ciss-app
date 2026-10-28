<?php

namespace App\View\Components\Employee;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

/**
 * Employee control menu built from config('ciss.employee_menu').
 * Items the user has no access to are rendered disabled; administrators get all.
 *
 * <x-employee.menu />                 desktop grid
 * <x-employee.menu variant="bottom" /> mobile bottom bar
 */
class Menu extends Component
{
    public array $items;

    public function __construct(public string $variant = 'grid')
    {
        $user = Auth::guard('web')->user();

        $this->items = collect(config('ciss.employee_menu'))
            ->reject(fn ($item) => ($item['in_menu'] ?? true) === false)
            ->map(function ($item, $key) use ($user) {
                $routeName = $item['route'] ?? null;

                return $item + [
                    'key' => $key,
                    'url' => $routeName && Route::has($routeName) ? route($routeName) : null,
                    'active' => $routeName && request()->routeIs($routeName),
                    'enabled' => ($item['always'] ?? false) || $user->canAccessMenu($key),
                ];
            })
            ->all();
    }

    public function render()
    {
        return view('components.employee.menu-' . $this->variant);
    }
}
