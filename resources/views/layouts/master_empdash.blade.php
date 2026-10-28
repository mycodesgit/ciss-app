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
    @include('layouts.partials.employee-topbar')

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
                    <footer class="text-center py-2 mt-6 text-secondary fixed-bottom bg-body border-top" style="z-index: 99">
                        <p class="mb-0">CISS V.1.0: Maintained and Managed by Management Information System Office (MISO) © 2023 CPSU, All Rights Reserved</p>
                    </footer>
                </div>
            </div>
        </div>

        <x-employee.menu variant="bottom" />
    </main>

    @include('layouts.partials.employee-body-end')
</body>

</html>
