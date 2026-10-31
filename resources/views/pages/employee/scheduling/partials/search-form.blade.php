{{--
    Campus (and term) search of a Scheduling page; submitting reloads the page's table (no page reload).
    @include(..., ['formId' => '...', 'withTerm' => false]) with the ScopesScheduling::schedulingOptions() variables.
--}}
@php($withTerm = $withTerm ?? true)

<div class="card card-animate mb-3">
    <div class="card-header pt-3">
        <h6 class="card-title"><i class="ti ti-search"></i> Search to show data</h6>
    </div>
    <div class="card-body">
        <form id="{{ $formId }}" autocomplete="off">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Campus: <span class="text-danger">*</span></label>
                    {{-- Administrators: every campus; everyone else: their own --}}
                    <select class="form-control form-control-sm" name="campus" required>
                        @foreach ($campuses as $code => $name)
                            <option value="{{ $code }}" @selected($code === $userCampus)>{{ str_replace(' Campus', '', $name) }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($withTerm)
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">School Year: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="schlyear" required>
                            @foreach ($schoolYears as $schoolYear)
                                <option value="{{ $schoolYear }}" @selected($schoolYear === $activeYear)>{{ $schoolYear }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Semester: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="semester" required>
                            @foreach ($semesters as $value => $label)
                                <option value="{{ $value }}" @selected($value === $activeSemester)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <div class="d-flex flex-column h-100">
                        <label class="form-label fw-semibold opacity-0 d-none d-md-block">Action</label>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="ti ti-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
