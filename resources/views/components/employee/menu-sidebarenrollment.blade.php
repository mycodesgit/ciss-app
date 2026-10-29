{{-- Sidebar for the Enrollment module (master_empenrollment) --}}
<ul class="nav flex-column">
    <li class="px-4 py-1"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.enrollment.dashboard') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.dashboard')])>
            <i class="ti ti-home"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>

    {{-- Pages not built yet link to # --}}
    <li>
        <a href="#" class="nav-link">
            <i class="ti ti-user-plus"></i><span class="nav-text">Add Student</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.enrollment.enroll.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.enroll.*')])>
            <i class="ti ti-device-laptop"></i><span class="nav-text">Enroll Student</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.enrollment.edit.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.enrollment.edit.*')])>
            <i class="ti ti-edit"></i><span class="nav-text">Edit Enrollment</span>
        </a>
    </li>
    <li>
        <a href="#" class="nav-link">
            <i class="ti ti-receipt"></i><span class="nav-text">Edit Dup. Fees</span>
        </a>
    </li>
    <li>
        <a href="#" class="nav-link">
            <i class="ti ti-scoreboard"></i><span class="nav-text">Evaluated Student</span>
        </a>
    </li>
    <li>
        <a href="#" class="nav-link">
            <i class="ti ti-history"></i><span class="nav-text">Enrollment History</span>
        </a>
    </li>
    <li>
        <a href="#" class="nav-link">
            <i class="ti ti-file-spreadsheet"></i><span class="nav-text">Gradesheet</span>
        </a>
    </li>
    <li>
        <a href="#" class="nav-link">
            <i class="ti ti-file-pencil"></i><span class="nav-text">Correction of Grades</span>
        </a>
    </li>
</ul>
