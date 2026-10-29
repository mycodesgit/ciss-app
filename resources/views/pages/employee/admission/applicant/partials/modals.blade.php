{{-- Modals of List of Applicant (filled and opened by applicants.js) --}}

{{-- View Data: application details (read-only) + editable personal information --}}
<x-employee.form-modal id="applicantInfoModal" title="Applicant Information" form-id="applicantInfoForm" url="update-url" method="PUT" size="xl">
    <h6 class="fw-bold text-success mb-2">Application</h6>
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label>Admission ID:</label>
            <input type="text" class="form-control form-control-sm" data-show="admission_id" readonly>
        </div>
        <div class="col-md-3">
            <label>Type:</label>
            <input type="text" class="form-control form-control-sm" data-show="type_name" readonly>
        </div>
        <div class="col-md-3">
            <label>Campus:</label>
            <input type="text" class="form-control form-control-sm" data-show="campus_name" readonly>
        </div>
        <div class="col-md-3">
            <label>Mobile No.:</label>
            <input type="text" class="form-control form-control-sm" data-show="contact" readonly>
        </div>
        <div class="col-md-4">
            <label>Email:</label>
            <input type="text" class="form-control form-control-sm" data-show="email" readonly>
        </div>
        <div class="col-md-4">
            <label>Course Preference 1:</label>
            <input type="text" class="form-control form-control-sm" data-show="preference_1" readonly>
        </div>
        <div class="col-md-4">
            <label>Course Preference 2:</label>
            <input type="text" class="form-control form-control-sm" data-show="preference_2" readonly>
        </div>
        <div class="col-md-4">
            <label>Last School Attended:</label>
            <input type="text" class="form-control form-control-sm" data-show="lstsch_attended" readonly>
        </div>
        <div class="col-md-2">
            <label>Strand:</label>
            <input type="text" class="form-control form-control-sm" data-show="strand" readonly>
        </div>
        <div class="col-md-3">
            <label>College Last Attended:</label>
            <input type="text" class="form-control form-control-sm" data-show="suc_lst_attended" readonly>
        </div>
        <div class="col-md-3">
            <label>Course (Transferee):</label>
            <input type="text" class="form-control form-control-sm" data-show="course" readonly>
        </div>
    </div>

    <h6 class="fw-bold text-success mb-2 pt-2 border-top">Personal Information <small class="text-muted fw-normal">(editable)</small></h6>
    <div class="row g-3">
        <div class="col-md-3">
            <label>First Name: <span class="text-danger">*</span></label>
            <input type="text" name="fname" class="form-control form-control-sm js-capitalize" required>
        </div>
        <div class="col-md-3">
            <label>Middle Name:</label>
            <input type="text" name="mname" class="form-control form-control-sm js-capitalize">
        </div>
        <div class="col-md-3">
            <label>Last Name: <span class="text-danger">*</span></label>
            <input type="text" name="lname" class="form-control form-control-sm js-capitalize" required>
        </div>
        <div class="col-md-3">
            <label>Ext.:</label>
            <select name="ext" class="form-control form-control-sm">
                <option value="">N/A</option>
                @foreach (['Jr.', 'Sr.', 'III', 'IV'] as $ext)
                    <option value="{{ $ext }}">{{ $ext }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Sex: <span class="text-danger">*</span></label>
            <select name="gender" class="form-control form-control-sm" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="col-md-3">
            <label>Birthday: <span class="text-danger">*</span></label>
            <input type="date" name="bday" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-3">
            <label>Civil Status: <span class="text-danger">*</span></label>
            <select name="civil_status" class="form-control form-control-sm" required>
                @foreach (['Single', 'Married', 'Divorced', 'Widowed', 'Separated'] as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <label>Address: <span class="text-danger">*</span></label>
            <input type="text" name="address" class="form-control form-control-sm" required>
        </div>
    </div>
</x-employee.form-modal>

{{-- Re-Upload: which documents the applicant must upload again --}}
<x-employee.form-modal id="reuploadModal" title="Re-Upload Access" form-id="reuploadForm" url="reupload-url" method="PUT">
    <label>Applicant:</label>
    <input type="text" class="form-control form-control-sm mb-3" data-show="display_name" readonly>

    <label>Allow the applicant to re-upload:</label>
    @foreach ($reuploadDocuments as $value => $label)
        <div class="icheck-success">
            <input type="checkbox" id="reupload-{{ $value }}" name="reuploadallow[]" value="{{ $value }}">
            <label for="reupload-{{ $value }}">{{ $label }}</label>
        </div>
    @endforeach
</x-employee.form-modal>

{{-- Schedule: exam date/time (from this year's schedules of your campus) and venue --}}
<x-employee.form-modal id="scheduleModal" title="Assign Exam Schedule" form-id="scheduleForm" url="schedule-url" method="PUT">
    <label>Applicant:</label>
    <input type="text" class="form-control form-control-sm mb-3" data-show="display_name" readonly>

    <label>Current Schedule:</label>
    <input type="text" class="form-control form-control-sm mb-3" data-show="schedule_label" data-empty="No schedule yet" readonly>

    <label>Exam Date & Time: <span class="text-danger">*</span></label>
    <select name="dateID" class="form-control form-control-sm mb-3" required>
        <option value="" disabled selected>Select schedule</option>
        @foreach ($schedules as $schedule)
            <option value="{{ $schedule['id'] }}" data-full="{{ $schedule['remaining'] === 0 ? 1 : 0 }}">
                {{ $schedule['label'] }} ({{ $schedule['remaining'] === 0 ? 'Full' : $schedule['remaining'] . ' slots left' }})
            </option>
        @endforeach
    </select>

    <label>Venue: <span class="text-danger">*</span></label>
    <select name="venue" class="form-control form-control-sm" required>
        <option value="" disabled selected>Select venue</option>
        @foreach ($venues as $venue)
            <option value="{{ $venue }}">{{ $venue }}</option>
        @endforeach
    </select>
</x-employee.form-modal>

{{-- School ID / Proof: shows the uploaded image (or a link for PDFs) --}}
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted small mb-2 js-document-note"></p>
                <img class="img-fluid rounded js-document-image d-none" alt="Uploaded document">
                <a class="btn btn-success btn-sm js-document-link d-none" target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf"></i> Open document
                </a>
                <p class="text-muted fs-5 my-5 js-document-empty d-none">No document uploaded.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
