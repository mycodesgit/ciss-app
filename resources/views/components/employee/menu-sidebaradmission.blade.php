{{-- Sidebar for the Admission module (master_empadmission) --}}
<ul class="nav flex-column">
    <li class="px-4 py-1"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.admission.dashboard') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.admission.dashboard')])>
            <i class="ti ti-box"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.admission.applicants.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.admission.applicants.*')])>
            <i class="ti ti-list-details"></i><span class="nav-text">List of Applicant</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.admission.examinees.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.admission.examinees.*')])>
            <i class="ti ti-file-check"></i><span class="nav-text">List of Examinee</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.admission.results.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.admission.results.*')])>
            <i class="ti ti-report-analytics"></i><span class="nav-text">Examination Results</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.admission.confirmed.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.admission.confirmed.*')])>
            <i class="ti ti-user-check"></i><span class="nav-text">Confirmed Applicants</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.admission.accepted.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.admission.accepted.*')])>
            <i class="ti ti-school"></i><span class="nav-text">Accepted Applicants</span>
        </a>
    </li>

    <li class="px-4 py-1 mt-2"><small class="nav-text text-muted">Configuration</small></li>
    <li>
        <a href="{{ route('employee.admission.slots.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.admission.slots.*')])>
            <i class="ti ti-id-badge"></i><span class="nav-text">Availability Slots</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.admission.config.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.admission.config.*')])>
            <i class="ti ti-settings"></i><span class="nav-text">Configure Admission</span>
        </a>
    </li>
</ul>
