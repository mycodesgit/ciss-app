{{-- Sidebar for the Admission module (master_empadmission) --}}
<ul class="nav flex-column">
    <li class="px-4 py-2"><small class="nav-text text-muted">Main Navigation</small></li>

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
</ul>
