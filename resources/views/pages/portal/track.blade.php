@extends('layouts.master_portal')

@section('title')
CISS V.1.0 || Track Admission
@endsection

@section('workspace')
    <div class="row">
        <div class="col-12">
            <div class="mb-6">
                <div class="card" style=" background-color: #e9ecef; margin-top: -10px">
                    <div class="card-body">
                        <ol class="breadcrumb" style="margin-bottom: -3px;">
                            <li class="breadcrumb-item">
                                <a href="{{ route('portal.admission') }}" wire:navigate class="btn btn-success btn-sm text-light">
                                    <i class="fas fa-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item active mt-1">Track Admission</li>
                        </ol>
                    </div>
                </div>

                <livewire:portal.track-admission />
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Dim the results while a search is running */
        .track-results {
            transition: opacity 0.2s ease;
        }

        .track-results.is-loading {
            opacity: 0.5;
            pointer-events: none;
        }

        /* New results / messages slide in */
        .track-fade {
            animation: trackFadeIn 0.35s ease both;
        }

        @keyframes trackFadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endpush
