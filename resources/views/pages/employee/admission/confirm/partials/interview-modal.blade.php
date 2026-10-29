{{-- Assign Interview Result (read-only for Guidance Officer / Staff) --}}
<x-employee.form-modal id="interviewModal" title="Assign Interview Result to Applicant" form-id="interviewForm" url="interview-url" method="PUT" :can-save="$canRecordInterviews">
    <div class="row g-3">
        <div class="col-md-6">
            <label>Applicant Name:</label>
            <input type="text" class="form-control form-control-sm" data-show="display_name" readonly>
        </div>
        <div class="col-md-6">
            <label>Strand:</label>
            <input type="text" class="form-control form-control-sm" data-show="strand" readonly>
        </div>
        <div class="col-md-6">
            <label>Course Preference 1:</label>
            <input type="text" class="form-control form-control-sm" data-show="preference_1" readonly>
        </div>
        <div class="col-md-6">
            <label>Course Preference 2:</label>
            <input type="text" class="form-control form-control-sm" data-show="preference_2" readonly>
        </div>
    </div>

    <hr>

    <fieldset @disabled(!$canRecordInterviews)>
        <div class="row g-3">
            <div class="col-md-6">
                <label>Rating: <span class="text-danger">*</span></label>
                <input type="number" name="rating" class="form-control form-control-sm" min="0" max="100" step="0.01" required>
            </div>
            <div class="col-md-6">
                <label>Remarks: <span class="text-danger">*</span></label>
                <select name="remarks" class="form-control form-control-sm" required>
                    <option value="" disabled selected>Select</option>
                    @foreach ($interviewRemarks as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <label>Course: <span class="text-danger">*</span></label>
                <select name="course" class="form-control form-control-sm" style="text-transform: uppercase;" required>
                    <option value="" disabled selected>Select Course</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->code }}">{{ $program->program }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <label>Comments: <small class="text-muted fst-italic">(optional)</small></label>
                <textarea name="reason" class="form-control form-control-sm" rows="2"></textarea>
            </div>
        </div>
    </fieldset>
</x-employee.form-modal>
