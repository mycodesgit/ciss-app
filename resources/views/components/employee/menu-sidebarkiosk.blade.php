{{-- Sidebar for the Kiosk module (master_empkiosk) --}}
<ul class="nav flex-column">
    <li class="px-4 py-1"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.kiosk.dashboard') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.kiosk.dashboard')])>
            <i class="ti ti-box"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.kiosk.users.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.kiosk.users.*')])>
            <i class="ti ti-users"></i><span class="nav-text">Kiosk User</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.kiosk.bulk.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.kiosk.bulk.*')])>
            <i class="ti ti-user-cog"></i><span class="nav-text">Bulk Generation</span>
        </a>
    </li>
</ul>
