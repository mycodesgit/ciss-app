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
