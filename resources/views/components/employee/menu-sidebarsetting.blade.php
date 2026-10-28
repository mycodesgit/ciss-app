{{-- Sidebar for the Accounts / Settings module (master_empsetting) --}}
<ul class="nav flex-column">
    <li class="px-4 py-2"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.settings.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.settings.index')])>
            <i class="ti ti-box"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>
</ul>
