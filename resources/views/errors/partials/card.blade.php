{{-- The error card (icon, code, title, message, buttons). Used inside the module workspace and on the standalone page. --}}
@php
    $inEmployeeArea = Auth::guard('web')->hasUser() || request()->is('employee', 'employee/*');

    $homeUrl = $inEmployeeArea && Route::has('employee.dashboard') ? route('employee.dashboard') : url('/');
    $homeLabel = $inEmployeeArea ? 'Dashboard' : 'Home';
@endphp

<style>
    .error-icon {
        width: 84px;
        height: 84px;
        font-size: 42px;
        background-color: rgba(4, 64, 31, 0.08);
        color: #04401f;
    }

    [data-bs-theme="dark"] .error-icon {
        background-color: rgba(101, 172, 134, 0.15);
        color: #65ac86;
    }

    .error-code {
        font-size: 4.5rem;
        line-height: 1;
        letter-spacing: -0.04em;
        color: #04401f;
    }

    [data-bs-theme="dark"] .error-code {
        color: #65ac86;
    }
</style>

<div class="card {{ $animate ?? false ? 'card-animate' : '' }} shadow-sm">
    <div class="card-body text-center p-5">
        <div class="error-icon rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
            <i class="ti ti-@yield('icon', 'alert-triangle')"></i>
        </div>

        <div class="error-code fw-bold mb-2">@yield('code')</div>
        <h2 class="h4 fw-bold mb-2">@yield('title')</h2>
        <p class="text-muted mb-4">@yield('message')</p>

        <div class="d-flex flex-wrap justify-content-center gap-2">
            @section('actions')
                <button type="button" class="btn btn-light btn-sm" onclick="history.length > 1 ? history.back() : location.assign(@js($homeUrl))">
                    <i class="ti ti-arrow-left"></i> Go Back
                </button>
                <a href="{{ $homeUrl }}" class="btn btn-success btn-sm">
                    <i class="ti ti-home"></i> {{ $homeLabel }}
                </a>
            @show
        </div>
    </div>
</div>
