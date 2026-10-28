<!DOCTYPE html>
<html lang="en" data-layout="main">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Logo -->
    <link rel="shortcut icon" type="" href="{{ asset('uilibs/images/cpsulogov4.png') }}">

    @include('layouts.partials.assets')
    @stack('styles')
    @stack('scripts')

</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-icons d-none d-md-flex">
            <i class="fas fa-book-open"></i>
            <i class="fas fa-building"></i>
            <i class="fas fa-graduation-cap"></i>
            <i class="fas fa-book-open"></i>
            <i class="fas fa-building"></i>
            <i class="fas fa-graduation-cap"></i>
        </div>
        @yield('body')
    </div>


    <script>
        $(document).ready(function() {
            @if(session('error'))
                toastr.error("{{ session('error') }}", "Error", {
                    closeButton: false,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: 10000
                });
            @endif

            @if(session('success'))
                toastr.success("{{ session('success') }}", "Success", {
                    closeButton: false,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: 10000
                });
            @endif
        });
    </script>

    <script data-navigate-once>
        document.addEventListener("mousemove", (e) => {
            const icons = document.querySelectorAll(".bg-icons i");

            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;

            icons.forEach((icon, index) => {
                const speed = (index + 1) * 0.5;

                icon.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
                icon.style.transition = "transform 0.2s ease-out";
            });
        });
    </script>

    @livewireScripts
</body>
</html>
