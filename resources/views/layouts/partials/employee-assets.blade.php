{{--
    <head> assets for the employee area (master_empdash).

    The first employee page after login is a full load, so everything here downloads
    once. After that, wire:navigate only swaps <body> and keeps these loaded.
--}}

@include('layouts.partials.livewire-head')

<link rel="stylesheet" href="{{ asset('uilibs/css/main.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/css/custom.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/fontawesome-free-V6/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/toastr/toastr.min.css') }}">

<style>
    a.disabled {
        pointer-events: none;
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<script>
    (function () {
        // Light is the default. Only a saved user choice can enable dark.
        function savedTheme() {
            try {
                return localStorage.getItem('theme') || 'light';
            } catch (e) {
                return 'light';
            }
        }

        function applyTheme() {
            var theme = savedTheme();
            document.documentElement.setAttribute('data-bs-theme', theme);

            var toggle = document.getElementById('themeToggle');
            if (!toggle) return;
            toggle.checked = theme === 'dark';
            document.getElementById('themeIcon').className = theme === 'dark' ? 'ti ti-sun' : 'ti ti-moon';
            document.getElementById('themeLabel').textContent = theme === 'dark' ? 'Light' : 'Dark';
        }

        document.addEventListener('change', function (e) {
            if (e.target.id !== 'themeToggle') return;
            try {
                localStorage.setItem('theme', e.target.checked ? 'dark' : 'light');
            } catch (err) { /* storage blocked: theme lasts for this page only */ }
            applyTheme();
        });

        // wire:navigate resets <html data-bs-theme> from the server markup, so re-apply after each swap
        document.addEventListener('livewire:navigating', function (e) {
            e.detail.onSwap(applyTheme);
        });
        document.addEventListener('livewire:navigated', applyTheme);

        applyTheme();

        // Back/forward cache restored a frozen copy of this page (e.g. Back after logout):
        // reload so the server checks the session and redirects to login if it has ended
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) window.location.reload();
        });
    })();
</script>

<script src="{{ asset('uilibs/js/main.js') }}"></script>
<script src="{{ asset('uilibs/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/toastr/toastr.min.js') }}"></script>
{{-- Tracked: wire:navigate does a full reload when crossing into the public pages (assets) --}}
<script src="{{ asset('uilibs/js/contextmenu.js') }}?area=employee" data-navigate-track></script>
