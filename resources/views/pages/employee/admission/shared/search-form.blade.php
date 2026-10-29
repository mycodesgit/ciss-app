{{-- Search form of the admission list pages. Searching reloads the table (no page reload), see admission/common.js --}}
<form id="admissionSearchForm">
    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label fw-semibold">Year: <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm" name="year">
                @foreach ($years as $year)
                    <option value="{{ $year }}" @selected($year == $currentYear)>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">Campus: <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm" name="campus">
                @foreach ($campuses as $code => $name)
                    <option value="{{ $code }}" @selected($code === $userCampus)>{{ str_replace(' Campus', '', $name) }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Strand:</label>
            <select class="form-control form-control-sm" name="strand">
                <option value="">All strands</option>
                @foreach ($strands as $strand)
                    <option value="{{ $strand->code }}">{{ $strand->strand }}</option>
                @endforeach
            </select>
        </div>

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
