@php
    $employee = Auth::guard('web')->user();
@endphp
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8" />
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('uilibs/images/cpsulogov4.png') }}">

    @include('layouts.partials.employee-assets')
    @stack('styles')
    @stack('scripts')
</head>

<body>
    <div id="overlay" class="overlay"></div>
    <!-- TOPBAR -->
    <nav id="topbar" class="navbar bg-white border-bottom fixed-top px-3" style="background-color: #04401f !important; z-index: 9995">
        <div id="toggleBtn" class="text-light" style="padding-left: 25px">
            CISS v.1.0
        </div>
        <div>
            <ul class="list-unstyled d-flex align-items-center mb-0 gap-1 d-none d-md-flex">
                <li>
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
                <li class="ms-3 dropdown">
                    <a href="#" role="button" class="text-light" data-bs-toggle="dropdown" aria-expanded="false">
                        Logged as: {{ $employee->full_name }} - {{ str_replace(' Campus', '', $employee->campus_name) }}
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
            </ul>
            <ul class="list-unstyled d-flex align-items-center mb-0 gap-1 d-md-none">
                <li class="ms-3">
                    <span class="text-light">{{ str_replace(' Campus', '', $employee->campus_name) }}</span>
                </li>
            </ul>
        </div>
    </nav>

    {{-- Logout must be a POST with CSRF; both "Sign Out" links submit this form --}}
    <form id="employee-logout-form" method="POST" action="{{ route('employee.logout') }}" class="d-none">
        @csrf
    </form>

    <!-- DASHBOARD MENU -->
    <x-employee.menu />

    <!-- MAIN CONTENT -->
    <main id="content" class="py-5">
        <div class="container-fluid">
            <div class="row">
                <div style="z-index: 9999">
                    <img src="{{ asset('uilibs/images/cpsulogov4.webp') }}" style="width:70px;" class="center-top">
                </div>
            </div>

            @section('workspace')
            @show

            <div class="row d-none d-md-block">
                <div class="col-12">
                    <footer class="text-center py-2 mt-6 text-secondary fixed-bottom">
                        <p class="mb-0">CISS V.1.0: Maintained and Managed by Management Information System Office (MISO) under the Leadership of Dr. Aladino C. Moraca Copyright © 2023 CPSU, All Rights Reserved</p>
                    </footer>
                </div>
            </div>
        </div>

        <x-employee.menu variant="bottom" />
    </main>

    <script>
        // Body scripts re-run on every wire:navigate visit
        @if (Session::has('error'))
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right'
            };
            toastr.error(@json(session('error')));
        @endif

        document.querySelectorAll('.card-animate').forEach((card, index) => {
            setTimeout(() => card.classList.add('show'), index * 100); // stagger effect
        });
    </script>

    @livewireScripts
</body>

</html>
