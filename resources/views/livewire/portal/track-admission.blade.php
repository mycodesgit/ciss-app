@php
    $a = $applicant ?? [];
    $value = fn ($key) => $a[$key] ?? null;
@endphp

<div class="row g-3 mb-3 mt-3">
    <div class="col-md-12">
        {{-- wire:ignore.self keeps the "show" class added by the layout's card-animate script --}}
        <div class="card card-animate" wire:ignore.self>
            <div class="card-body p-4">
                @if ($notFound)
                    <div class="alert alert-danger track-fade" wire:key="not-found-{{ $lname }}-{{ $fname }}">
                        We couldn’t find any records matching your criteria.
                    </div>
                @endif

                <form wire:submit="search">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="lname">Last Name: <span class="text-danger">*</span></label>
                            <input type="text" id="lname" wire:model="lname" placeholder="Enter Last Name" class="form-control form-control-sm @error('lname') is-invalid @enderror" oninput="this.value = this.value.toUpperCase()">
                            @error('lname') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="fname">First Name: <span class="text-danger">*</span></label>
                            <input type="text" id="fname" wire:model="fname" placeholder="Enter First Name" class="form-control form-control-sm @error('fname') is-invalid @enderror" oninput="this.value = this.value.toUpperCase()">
                            @error('fname') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-success w-100 btn-sm text-light" wire:loading.attr="disabled" wire:target="search">
                                <span wire:loading.remove wire:target="search">Search</span>
                                <span wire:loading wire:target="search">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Searching...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Dims while searching; the keyed block below is replaced (and fades in) for each new result --}}
    <div class="col-12 track-results" wire:loading.class="is-loading" wire:target="search">
        <div class="row g-3 track-fade" wire:key="result-{{ $value('admission_id') ?? 'empty' }}">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <h2 class="fs-4"><i class="ti ti-user"></i> Applicant Information</h2>
                        <hr>
                        <div class="mt-4 table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    @foreach ([
                                        'Admission ID' => 'admission_id',
                                        'Name' => 'name',
                                        'Preferred Campus' => 'campus',
                                        'Date of Birth' => 'bday',
                                        'Email Address' => 'email',
                                        'Contact No.' => 'contact',
                                        'Present Address' => 'address',
                                    ] as $label => $key)
                                        <tr>
                                            <th style="width: 35%">{{ $label }}:</th>
                                            <td style="font-style: italic">{{ $value($key) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <h2 class="fs-4"><i class="ti ti-checklist"></i> Admission Status</h2>
                        <hr>
                        @php
                            $steps = [
                                ['title' => 'Application', 'done' => (bool) $applicant],
                                ['title' => 'Examination Schedule', 'done' => $value('exam_date') && $value('exam_time') && $value('exam_venue')],
                                ['title' => 'Examination Results', 'done' => $value('stanine') !== null],
                                ['title' => 'Screening', 'done' => (bool) $value('screened')],
                                ['title' => 'Acceptance', 'done' => (bool) $value('accepted')],
                                ['title' => 'Enrollment', 'done' => (bool) $value('enrolled')],
                            ];
                        @endphp

                        <div class="tracking-list">
                            @foreach ($steps as $step)
                                <div class="tracking-item">
                                    <div class="tracking-icon {{ $step['done'] ? 'status-delivered' : 'status-intransit' }}">
                                        <i class="{{ $step['done'] ? 'ti ti-check fa-1x' : 'fas fa-times fa-2x' }}"></i>
                                    </div>

                                    <div class="tracking-date">
                                        @if ($loop->first && $applicant)
                                            <span>{{ $value('submitted_date') }}</span><span>{{ $value('submitted_time') }}</span>
                                        @else
                                            <span>&nbsp;</span><span>&nbsp;</span>
                                        @endif
                                    </div>

                                    <div class="tracking-content">
                                        {{ $step['title'] }}
                                        @if (!$applicant)
                                            <span>&nbsp;</span>
                                        @elseif ($loop->index === 0)
                                            <span>Submitted/Recorded Applicant Information</span>
                                        @elseif ($loop->index === 1)
                                            <span>
                                                Date: @include('livewire.portal.partials.track-value', ['text' => $value('exam_date')]),
                                                <br>
                                                Time: @include('livewire.portal.partials.track-value', ['text' => $value('exam_time')]),
                                                <br>
                                                Venue: @include('livewire.portal.partials.track-value', ['text' => $value('exam_venue')])
                                            </span>
                                        @elseif ($loop->index === 2)
                                            <span>
                                                @if ($value('results_released'))
                                                    Stanine: @include('livewire.portal.partials.track-value', ['text' => $value('stanine')]),
                                                    Remarks: @include('livewire.portal.partials.track-value', ['text' => $value('percentile')])
                                                @else
                                                    <i class="text-warning">The result will be posted on {{ $value('results_release_label') }}</i>
                                                @endif
                                            </span>
                                        @elseif ($loop->index === 3)
                                            <span>Remarks: @include('livewire.portal.partials.track-value', ['text' => $value('screening_remark'), 'danger' => $value('screening_remark') === 'Failed'])</span>
                                        @elseif ($loop->index === 4)
                                            <span>Accepted for the program: @include('livewire.portal.partials.track-value', ['text' => $value('accepted_course')])</span>
                                        @else
                                            <span>Status: @include('livewire.portal.partials.track-value', ['text' => $value('enrolled') ? 'Proceed to Registrar for Enrollment' : null])</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
