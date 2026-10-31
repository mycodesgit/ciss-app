{{-- Sidebar for the Scheduling module (master_empscheduling): only the pages the user may open (config ciss.scheduling.pages) --}}
@php
    $user = Auth::guard('web')->user();
    $pages = [
        'colleges' => ['label' => 'Colleges', 'icon' => 'ti ti-building'],
        'programs' => ['label' => 'Programs', 'icon' => 'ti ti-book'],
        'rooms' => ['label' => 'Class Rooms', 'icon' => 'ti ti-route-square'],
        'classes' => ['label' => 'Classes Enrolled', 'icon' => 'ti ti-bookmark-plus'],
        'curricula' => ['label' => 'Curriculum Programs', 'icon' => 'ti ti-notes'],
        'designations' => ['label' => 'Faculty Designation', 'icon' => 'ti ti-assembly'],
        'faculty' => ['label' => 'Faculty List', 'icon' => 'ti ti-users'],
        'offerings' => ['label' => 'Subject Offered', 'icon' => 'ti ti-books'],
    ];
@endphp

<ul class="nav flex-column">
    <li class="px-4 py-1"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.scheduling.dashboard') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.scheduling.dashboard')])>
            <i class="ti ti-home"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>

    @foreach ($pages as $page => $item)
        @if ($user->canOpenSchedulingPage($page))
            <li>
                <a href="{{ route("employee.scheduling.{$page}.index") }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs("employee.scheduling.{$page}.*")])>
                    <i class="{{ $item['icon'] }}"></i><span class="nav-text">{{ $item['label'] }}</span>
                </a>
            </li>
        @endif
    @endforeach
</ul>
