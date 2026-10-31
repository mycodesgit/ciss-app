{{-- Sidebar for the Queueing module (master_empqueue) --}}
<ul class="nav flex-column">
    <li class="px-4 py-1"><small class="nav-text text-muted">Main Navigation</small></li>

    <li>
        <a href="{{ route('employee.queue.dashboard') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.queue.dashboard')])>
            <i class="ti ti-table"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.queue.counters.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.queue.counters.*')])>
            <i class="ti ti-window"></i><span class="nav-text">Counters</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.queue.numbers.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.queue.numbers.*')])>
            <i class="ti ti-numbers"></i><span class="nav-text">Numbers</span>
        </a>
    </li>
    <li>
        <a href="{{ route('employee.queue.settings.index') }}" wire:navigate @class(['nav-link', 'active' => request()->routeIs('employee.queue.settings.*')])>
            <i class="ti ti-settings"></i><span class="nav-text">Settings</span>
        </a>
    </li>

    <li class="px-4 py-1 mt-2"><small class="nav-text text-muted">Display</small></li>
    <li>
        {{-- The public TV screen opens in a new tab (no login) --}}
        <a href="{{ route('queue-monitor.show', Auth::guard('web')->user()->campus ? trim(explode(',', Auth::guard('web')->user()->campus)[0]) : 'MC') }}" target="_blank" rel="noopener" class="nav-link">
            <i class="ti ti-device-tv"></i><span class="nav-text">Queue Monitor</span>
        </a>
    </li>
</ul>
