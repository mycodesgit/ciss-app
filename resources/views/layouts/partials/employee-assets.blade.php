{{--
    <head> assets for every employee layout (master_empdash, master_empmodule).

    The first employee page after login is a full load, so everything here downloads
    once. After that, wire:navigate only swaps <body> and keeps these loaded, which is
    why both layouts must include this same partial.
--}}

@include('layouts.partials.livewire-head')

<link rel="stylesheet" href="{{ asset('uilibs/css/main.css') }}">
{{-- Versioned like x-script: ?v= reloads it whenever it changes; data-navigate-track does one full reload with wire:navigate --}}
<link rel="stylesheet" href="{{ asset('uilibs/css/custom.css') }}?v={{ @filemtime(public_path('uilibs/css/custom.css')) }}" data-navigate-track>
<link rel="stylesheet" href="{{ asset('uilibs/plugins/fontawesome-free-V6/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/toastr/toastr.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('uilibs/plugins/fullcalendar/fullcalendar.css') }}">

<style>
    a.disabled {
        pointer-events: none;
        opacity: 0.5;
        cursor: not-allowed;
    }

    .sticky-column {
        position: sticky;
        top: 50px;
        height: 5vh;
    }

    .scrolling-column {
        overflow-y: auto;
    }

    .my-custom-show-animation {
        animation: myShowAnimation 0.2s ease forwards;
    }

    @keyframes myShowAnimation {
        from {
            transform: scale(0.5);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .btn-block {
        display: block;
        width: 100%;
    }

    /* Footer on sidebar layouts: sits beside the sidebar (240px, 60px when collapsed), full width on mobile */
    .content .content-footer {
        left: 240px;
        transition: left 0.3s;
    }

    .content.full .content-footer {
        left: 60px;
    }

    @media (max-width: 992px) {
        .content .content-footer {
            left: 0;
        }
    }
</style>

<script>
    (function () {
        // ---------- Theme (light by default; only a saved choice enables dark) ----------
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

        // ---------- Sidebar / topbar buttons ----------
        // main.js binds these once at load, before <body> exists here, and wire:navigate
        // replaces the elements anyway. Delegated listeners on document always work.
        document.addEventListener('click', function (e) {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('overlay');

            if (e.target.closest('#toggleBtn')) {
                sidebar && sidebar.classList.toggle('collapsed');
                document.getElementById('content')?.classList.toggle('full');
                document.getElementById('topbar')?.classList.toggle('full');
            } else if (e.target.closest('#mobileBtn')) {
                sidebar && sidebar.classList.add('mobile-show');
                overlay && overlay.classList.add('show');
            } else if (e.target.closest('#overlay')) {
                sidebar && sidebar.classList.remove('mobile-show');
                overlay && overlay.classList.remove('show');
            }
        });

        // ---------- Sidebar scroll ----------
        // The sidebar scrolls on its own (fixed, 100vh, overflow-y-auto); center the active menu item in it.
        // Layouts without a sidebar (dashboard) are skipped.
        function scrollToActiveMenu() {
            var sidebar = document.getElementById('sidebar');
            var activeItem = sidebar && sidebar.querySelector('.active');
            if (!activeItem) return;

            var itemTop = activeItem.getBoundingClientRect().top - sidebar.getBoundingClientRect().top + sidebar.scrollTop;
            sidebar.scrollTo({
                top: itemTop - sidebar.clientHeight / 2 + activeItem.offsetHeight / 2,
                behavior: 'smooth'
            });
        }

        // wire:navigate resets <html data-bs-theme> from the server markup, so re-apply after each swap
        document.addEventListener('livewire:navigating', function (e) {
            e.detail.onSwap(applyTheme);
        });
        document.addEventListener('livewire:navigated', applyTheme);
        // Fires on the first load too (Livewire 3), and after each wire:navigate swaps in a new sidebar
        document.addEventListener('livewire:navigated', scrollToActiveMenu);

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
<script src="{{ asset('uilibs/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/jquery-validation/additional-methods.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/fullcalendar/fullcalendar.js') }}"></script>
{{-- DataTables & export buttons --}}
<script src="{{ asset('uilibs/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('uilibs/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('uilibs/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
{{-- Shared "DataTable + modal forms" behaviour for employee pages (CissCrud) --}}
<x-script src="js/employee/crud.js" />
{{-- Shared address fields (x-employee.address-fields): Region > Province > City > Barangay (CissAddress) --}}
<x-script src="js/employee/address.js" />
{{-- Tracked: wire:navigate does a full reload when crossing into the public pages (assets) --}}
<script src="{{ asset('uilibs/js/contextmenu.js') }}?area=employee" data-navigate-track></script>
