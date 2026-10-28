{{-- Sidebar for the Accounts / Settings module (master_empsetting) --}}
<ul class="nav flex-column">
    <li class="px-4 py-2"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.settings.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.settings.index')])>
            <i class="ti ti-box"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>

    @if (Auth::guard('web')->user()->isAdministrator())
        <li>
            <a href="{{ route('employee.settings.users.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.settings.users.*')])>
                <i class="ti ti-users"></i><span class="nav-text">User's Management</span>
            </a>
        </li>
    @endif
</ul>
