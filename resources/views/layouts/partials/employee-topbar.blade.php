{{-- Employee topbar. Pass ['withSidebar' => true] on layouts that have a sidebar (adds the mobile menu button). --}}
@php
    $employee = Auth::guard('web')->user();
    $campusShort = str_replace(' Campus', '', $employee->campus_name);
@endphp

<div id="overlay" class="overlay"></div>
<nav id="topbar" class="navbar bg-white border-bottom fixed-top px-3" style="background-color: #04401f !important; z-index: 9995">
    <div id="toggleBtn" class="text-light" style="padding-left: 25px">
        CISS v.1.0
    </div>
    <div>
        <ul class="list-unstyled d-flex align-items-center mb-0 gap-1">
            @if ($withSidebar ?? false)
                <li>
                    <button type="button" id="mobileBtn" class="btn btn-outline-light btn-icon btn-sm d-lg-none me-2">
                        <i class="fas fa-bars"></i>
                    </button>
                </li>
            @endif
            <li class="d-none d-md-block">
                <label class="theme-switch" for="themeToggle">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider">
                        <span class="slider-content">
                            <i id="themeIcon" class="ti ti-moon"></i>
                            <span id="themeLabel">Dark</span>
                        </span>
                    </span>
                </label>
            </li>
            <li class="ms-3 dropdown d-none d-md-block">
                <a href="#" role="button" class="text-light" data-bs-toggle="dropdown" aria-expanded="false">
                    Logged as: {{ $employee->full_name }} - {{ $campusShort }}
                </a>
                <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 200px;">
                    <div class="d-flex gap-3 align-items-center border-dashed border-bottom px-3 py-3">
                        <img src="{{ asset('uilibs/images/user.png') }}" alt="" class="avatar avatar-md rounded-circle" />
                        <div>
                            <h4 class="mb-0 small">{{ $employee->full_name }}</h4>
                            <p class="mb-0 small text-success">{{ $employee->role_name }}</p>
                        </div>
                    </div>
                    <div class="p-3 d-flex flex-column gap-1 small lh-lg">
                        <a href="#!">
                            <i class="ti ti-activity"></i> <span> Activity</span>
                        </a>
                        <a href="{{ route('employee.logout') }}" class="text-danger" onclick="event.preventDefault(); document.getElementById('employee-logout-form').requestSubmit()">
                            <i class="ti ti-logout"></i> <span> Sign Out</span>
                        </a>
                    </div>
                </div>
            </li>
            <li class="ms-3 d-md-none">
                <span class="text-light">{{ $campusShort }}</span>
            </li>
        </ul>
    </div>
</nav>

{{-- Logout must be a POST with CSRF; every "Sign Out" link submits this form --}}
<form id="employee-logout-form" method="POST" action="{{ route('employee.logout') }}" class="d-none">
    @csrf
</form>
