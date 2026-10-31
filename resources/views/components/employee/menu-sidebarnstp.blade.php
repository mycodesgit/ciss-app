{{-- Sidebar for the NSTP module (master_empnstp) --}}
<ul class="nav flex-column">
    <li class="px-4 py-1"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.nstp.dashboard') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.nstp.dashboard')])>
            <i class="ti ti-home"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>

    {{-- One list per component (config ciss.nstp_components) --}}
    @php
        $icons = ['cwts' => 'ti ti-users', 'lts' => 'ti ti-user-cog', 'rotc' => 'ti ti-user-bolt', 'nstp' => 'ti ti-users-group'];
    @endphp
    @foreach (config('ciss.nstp_components') as $key => $component)
        <li>
            <a href="{{ route('employee.nstp.students.index', $key) }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.nstp.students.*') && request()->route('component') === $key])>
                <i class="{{ $icons[$key] ?? 'ti ti-users' }}"></i><span class="nav-text">{{ $component['label'] }} Students</span>
            </a>
        </li>
    @endforeach

    <li>
        <a href="{{ route('employee.nstp.gradesheet.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.nstp.gradesheet.*')])>
            <i class="ti ti-numbers"></i><span class="nav-text">Grade Sheet</span>
        </a>
    </li>

    <li class="px-4 py-1 mt-2"><small class="nav-text text-muted">Reports</small></li>
    <li>
        <a href="{{ route('employee.nstp.enrollees.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.nstp.enrollees.*')])>
            <i class="ti ti-brand-miniprogram"></i><span class="nav-text">CWTS/LTS/ROTC</span>
        </a>
    </li>
</ul>
