@extends('layouts.master_portal')

@section('title')
    CISS - Admission Application Submitted
@endsection

@section('workspace')
    <div class="row">
        <div class="col-12">
            <div class="card card-animate shadow-sm rounded-4 my-4 overflow-hidden">
                <div class="bg-success" style="height: 6px;"></div>
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3 p-3" style="width: 64px; height: 64px;">
                        <i class="fas fa-check fa-2x"></i>
                    </div>

                    <h4 class="fw-bold text-dark mb-2">{{ $year }} Admission Test Application Submitted</h4>
                    <p class="text-muted mb-3 mx-auto" style="max-width: 520px;">
                        {{ session('success') }}
                    </p>

                    <div class="bg-light border rounded-3 p-3 mb-4 mx-auto text-start" style="max-width: 420px;">
                        <div><strong>Admission ID:</strong> {{ $admissionId }}</div>
                        <div><strong>Email:</strong> {{ $email }}</div>
                        <small class="text-muted">A confirmation was sent to your email. Keep your Admission ID to track your application.</small>
                    </div>

                    <a href="{{ route('portal.admission') }}" wire:navigate class="btn btn-success px-4 py-2 rounded-pill">
                        <i class="fas fa-home me-1"></i> Back to Portal Home
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
