{{--
    Shared layout for the error pages (404, 419, 500, ...). Laravel picks errors/{code}.blade.php,
    then errors/4xx or errors/5xx. Each page fills: code, title, message, icon (and optionally actions).

    $errorShell / $errorModule come from App\Exceptions\Handler: a logged-in employee gets the
    module layout (sidebar + workspace); everyone else, or a failed module render, gets errors.standalone.
--}}
@php
    $errorShell ??= 'errors.standalone';
    $inModule = $errorShell !== 'errors.standalone';
@endphp

@extends($errorShell)

@section('workspace')
    @if ($inModule)
        <div class="row">
            <div class="col-12">
                <div class="mb-6">
                    <div class="card mb-3" style=" background-color: #e9ecef; margin-top: -10px">
                        <div class="card-body">
                            <ol class="breadcrumb" style="margin-bottom: -3px;">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('employee.dashboard') }}" wire:navigate class="btn btn-success btn-sm text-light">
                                        <i class="fas fa-home"></i>
                                    </a>
                                </li>
                                @if ($errorModule ?? null)
                                    <li class="breadcrumb-item mt-1">{{ $errorModule }}</li>
                                @endif
                                <li class="breadcrumb-item active mt-1">Error @yield('code')</li>
                            </ol>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">@yield('title')</h1>
                            <p class="text-muted small mb-0">The page could not be shown.</p>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            @include('errors.partials.card', ['animate' => true])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('errors.partials.card')
    @endif
@endsection
