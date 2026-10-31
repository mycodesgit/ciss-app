{{--
    Error page shell when there is no employee session (or the module layout could not render).
    Self-contained on purpose: only main.css/custom.css, no libraries, no database queries,
    so a 500 caused by a broken database or session still renders.
--}}
@php
    // hasUser(): the employee is already loaded for this request (no query, safe on a 500)
    $employee = Auth::guard('web')->hasUser() ? Auth::guard('web')->user() : null;
@endphp
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('code') | @yield('title') - CISS</title>

    <link rel="icon" type="image/png" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
    <link rel="stylesheet" href="{{ asset('uilibs/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('uilibs/css/custom.css') }}?v={{ @filemtime(public_path('uilibs/css/custom.css')) }}">

    <script>
        // Same saved theme as the employee pages (light unless dark was chosen)
        try {
            document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
        } catch (e) { /* storage blocked: stay light */ }
    </script>
</head>

<body>
    <!-- TOPBAR (static copy of the employee/portal topbar: no menus, no queries) -->
    <nav id="topbar" class="navbar bg-white border-bottom fixed-top px-3" style="background-color: #04401f !important; z-index: 9995">
        <div class="text-light" style="padding-left: 25px">
            CISS v.1.0
        </div>
        @if ($employee)
            <div class="text-light d-none d-md-block">
                Logged as: {{ $employee->full_name }}
            </div>
        @endif
    </nav>

    <!-- MAIN CONTENT -->
    <main id="content" class="py-10">
        <div class="container">
            <div class="row">
                <div>
                    <img src="{{ asset('uilibs/images/cpsulogov4.webp') }}" style="width:70px;" class="center-top" alt="CPSU">
                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 14rem);">
                <div class="w-100" style="max-width: 560px;">
                    @yield('workspace')
                </div>
            </div>

            <div class="row d-none d-md-block">
                <div class="col-12">
                    <footer class="text-center py-2 mt-6 text-secondary fixed-bottom bg-body border-top" style="z-index: 99">
                        <p class="mb-0">CISS V.1.0: Maintained and Managed by Management Information System Office (MISO) © 2023 CPSU, All Rights Reserved</p>
                    </footer>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
