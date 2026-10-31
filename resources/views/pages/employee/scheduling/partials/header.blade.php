{{-- Breadcrumb and title of a Scheduling page: @include(..., ['page' => 'Class Rooms', 'title' => '...', 'description' => '...']) --}}
<div class="card mb-3" style=" background-color: #e9ecef; margin-top: -10px">
    <div class="card-body">
        <ol class="breadcrumb" style="margin-bottom: -3px;">
            <li class="breadcrumb-item">
                <a href="{{ route('employee.dashboard') }}" wire:navigate class="btn btn-success btn-sm text-light">
                    <i class="fas fa-home"></i>
                </a>
            </li>
            <li class="breadcrumb-item mt-1">Scheduling</li>
            <li class="breadcrumb-item active mt-1">{{ $page }}</li>
        </ol>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h1 class="h4 fw-bold mb-1" style="letter-spacing: -0.02em;">{{ $title }}</h1>
        <p class="text-muted small mb-0">{{ $description }}</p>
    </div>
</div>
