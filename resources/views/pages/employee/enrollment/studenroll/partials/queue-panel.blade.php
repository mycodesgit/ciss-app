{{--
    Queue counter of the logged-in user (Main Campus, queue On). counterstatus: 1 Closed, 2 Open, 3 Hold.
    Status, Next, Call and Save Transaction: EnrollmentQueueController via enroll.js
--}}
@php
    use App\Http\Controllers\Employee\Enrollment\EnrollmentQueueController;

    $status = (int) ($queueCounter->counterstatus ?? 1);
@endphp

<div class="row g-3 justify-content-center" id="queuePanel" data-counter-id="{{ $queueCounter->id ?? '' }}">
    @if (! $queueCounter)
        <div class="col-md-12">
            <div class="alert alert-secondary text-center mb-0">
                <i class="ti ti-info-circle me-1"></i> You have no queue counter assigned.
            </div>
        </div>
    @else
        <!-- Counter Status Switcher Card -->
        <div class="col-md-12">
            <div class="card card-animate">
                <div class="card-header pt-3">
                    <h6 class="card-title">
                        <i class="ti ti-settings"></i> Queue Control Status
                    </h6>
                </div>
                <div class="card-body p-3">
                    <label for="counterStatusSelect" class="form-label fw-bold text-muted small">Change Control Status</label>
                    <select id="counterStatusSelect" class="form-select">
                        @foreach (EnrollmentQueueController::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- CLOSED STATE (1) -->
        <div @class(['col-md-12 js-queue-status', 'd-none' => $status !== 1]) data-status="1">
            <div class="alert alert-danger text-center fw-bold mb-0 shadow-sm" role="alert">
                <i class="ti ti-lock me-1"></i> Queueing Control is Closed
            </div>
        </div>

        <!-- PAUSED / WAITING STATE (3) -->
        <div @class(['col-md-12 js-queue-status', 'd-none' => $status !== 3]) data-status="3">
            <div class="alert alert-warning text-center fw-bold mb-0 shadow-sm" role="alert">
                <i class="ti ti-player-pause me-1"></i> Queueing Control is Paused/Waiting
            </div>
        </div>

        <!-- OPEN STATE (2) -->
        <div @class(['col-md-12 js-queue-status', 'd-none' => $status !== 2]) data-status="2">
            <div class="row g-3">
                <!-- Queue Control Card -->
                <div class="col-md-12">
                    <div class="card card-animate">
                        <div class="card-header pt-3">
                            <h6 class="card-title">
                                <i class="ti ti-server"></i> Current Serving
                            </h6>
                        </div>
                        <div class="card-body p-3 text-center">
                            <input type="text" id="queueNumber" class="form-control-plaintext text-dark fw-bolder display-6 text-center rounded-3" readonly value="{{ $servingNumber ?? '--' }}">

                            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center mt-3">
                                <button type="button" id="nextButton" class="btn btn-success fw-bold shadow-sm">
                                    <i class="ti ti-player-track-next me-1"></i> Next
                                </button>
                                <button type="button" id="callButton" class="btn btn-outline-danger fw-bold">
                                    <i class="ti ti-phone-call me-1"></i> Call
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Category Card -->
                <div class="col-md-12">
                    <div class="card card-animate">
                        <div class="card-header pt-3">
                            <h6 class="card-title">
                                <i class="ti ti-server"></i> Select Transaction
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <form id="transactionForm">
                                <div class="mb-3">
                                    <select name="category" class="form-select form-select-md text-secondary fw-semibold">
                                        @foreach (EnrollmentQueueController::CATEGORIES as $category)
                                            <option value="{{ $category }}" @selected($queueCounter->category === $category)>{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success fw-bold shadow-sm">
                                        Save Transaction
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
