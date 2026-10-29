{{-- Sidebar for the Admission module (master_empadmission) --}}
<ul class="nav flex-column">
    <li class="px-4 py-2"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.admission.dashboard') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.admission.dashboard')])>
            <i class="ti ti-box"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>
</ul>
