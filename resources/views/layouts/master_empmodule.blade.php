{{--
    Base layout for employee modules with a sidebar (Settings, Admission, ...).
    A module layout extends this and fills @section('sidebar'); its pages fill 'title' and 'workspace'.
--}}
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <link rel="shortcut icon" sizes="180x180" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('uilibs/images/cpsulogov4.png') }}">

    @include('layouts.partials.employee-assets')
    @stack('styles')
    @stack('scripts')
</head>

<body>
    @include('layouts.partials.employee-topbar', ['withSidebar' => true])

    <!-- SIDEBAR -->
    <aside id="sidebar" class="sidebar overflow-y-auto overflow-x-hidden" style="z-index: auto">
        @yield('sidebar')
    </aside>

    <!-- MAIN CONTENT -->
    <main id="content" class="content py-10">
        <div class="container-fluid">
            <div class="row">
                <div>
                    <img src="{{ asset('uilibs/images/cpsulogov4.webp') }}" style="width:70px;" class="center-top">
                </div>
            </div>

            @section('workspace')
            @show

            <div class="row d-none d-md-block">
                <div class="col-12">
                    {{-- Starts at the sidebar's edge instead of running under it (see .content-footer) --}}
                    <footer class="content-footer text-center py-2 mt-6 text-secondary fixed-bottom bg-body border-top" style="z-index: 99">
                        <p class="mb-0">CISS V.1.0: Maintained and Managed by Management Information System Office (MISO) © 2023 CPSU, All Rights Reserved</p>
                    </footer>
                </div>
            </div>
        </div>
    </main>

    @include('layouts.partials.employee-body-end')
</body>

</html>
