{{--
    Shared <head> assets for master_main and master_portal.

    wire:navigate only swaps <body>. It keeps everything already loaded in <head>
    and adds whatever is new, so both layouts load the SAME stylesheets and
    libraries. Switching layouts then needs no downloads (no flicker); the inline
    script below just enables the stylesheets of the layout on <html data-layout>.
--}}

{{-- master_main stylesheets --}}
<link rel="stylesheet" href="{{ asset('uilibs/plugins/bootstrap/css/bootstrap.min.css') }}" data-layout="main">
<link rel="stylesheet" href="{{ asset('uilibs/css/login-style.css') }}" data-layout="main">

{{-- master_portal stylesheets --}}
<link rel="stylesheet" href="{{ asset('uilibs/css/main.css') }}" data-layout="portal">
<link rel="stylesheet" href="{{ asset('uilibs/css/custom.css') }}" data-layout="portal">
<link rel="stylesheet" href="{{ asset('uilibs/css/trackadmission.css') }}" data-layout="portal">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}" data-layout="portal">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}" data-layout="portal">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/select2/css/select2.min.css') }}" data-layout="portal">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" data-layout="portal">

{{-- Shared stylesheets --}}
<link rel="stylesheet" href="{{ asset('uilibs/plugins/fontawesome-free-V6/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/toastr/toastr.min.css') }}">

<script>
    (function () {
        // Uses CSSStyleSheet.disabled (not the <link disabled> attribute) so the
        // <link> HTML never changes and wire:navigate keeps reusing the same tags
        function applyLayoutStyles() {
            var layout = document.documentElement.dataset.layout;

            document.querySelectorAll('link[rel="stylesheet"][data-layout]').forEach(function (link) {
                if (link.sheet) link.sheet.disabled = link.dataset.layout !== layout;
            });
        }

        // Runs before first paint: this inline script waits for the stylesheets above
        applyLayoutStyles();

        // Runs right after wire:navigate swaps the page, before it is painted
        document.addEventListener('livewire:navigating', function (e) {
            e.detail.onSwap(applyLayoutStyles);
        });
    })();
</script>

{{-- Libraries (loaded once, kept across wire:navigate visits) --}}
<script src="{{ asset('uilibs/js/main.js') }}"></script>
<script src="{{ asset('uilibs/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/jquery-validation/additional-methods.min.js') }}"></script>
<script src="{{ asset('uilibs/js/contextmenu.js') }}"></script>

@livewireStyles
