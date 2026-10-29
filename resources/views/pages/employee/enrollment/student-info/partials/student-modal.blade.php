{{--
    One student: header, Information tab (the edit form) and History tab (Enrollment History's route).
    Fields marked data-show="field" are read-only and filled from the student; the rest are saved by CissCrud.
--}}
<x-employee.form-modal id="studentInfoModal" title="Student Information" form-id="studentInfoForm" url="update-url" method="PUT" size="xl">
    <div class="card border-0 mb-3" style="background: linear-gradient(135deg, #04401f 0%, #0a6b3a 100%);">
        <div class="card-body p-4 d-flex align-items-center gap-3 text-white">
            <img src="{{ asset('uilibs/images/user.png') }}" alt="Student Photo" class="rounded-circle border border-white border-3" style="width: 80px; height: 80px; object-fit: cover;">
            <div>
                <h4 class="fw-bold mb-1 text-white js-student-name"></h4>
                <p class="mb-1 opacity-75"><i class="fas fa-id-card me-2"></i>Student ID: <span class="js-student-id"></span></p>
                <div class="d-flex gap-3 flex-wrap small">
                    <span><i class="fas fa-graduation-cap me-1"></i><span class="js-student-course"></span></span>
                    <span><i class="fas fa-school me-1"></i><span class="js-student-campus"></span></span>
                    <span><i class="fas fa-calendar-alt me-1"></i>Admitted: <span class="js-student-admission"></span></span>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills bg-light p-2 rounded-2 mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active js-first-tab" data-bs-toggle="pill" data-bs-target="#studentInfoTab" type="button" role="tab">
                <i class="ti ti-user"></i> Information
            </button>
        </li>
        <li class="nav-item ms-1" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#studentHistoryTab" type="button" role="tab">
                <i class="fas fa-graduation-cap"></i> Enrollment History
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="studentInfoTab" role="tabpanel">
            <h6 class="fw-bold text-success mb-2">Personal Information</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Student ID No.:</label>
                    <input type="text" class="form-control form-control-sm" data-show="stud_id" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">First Name: <span class="text-danger">*</span></label>
                    <input type="text" name="fname" class="form-control form-control-sm js-capitalize" maxlength="255" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Middle Name:</label>
                    <input type="text" name="mname" class="form-control form-control-sm js-capitalize" maxlength="255">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Last Name: <span class="text-danger">*</span></label>
                    <input type="text" name="lname" class="form-control form-control-sm js-capitalize" maxlength="255" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Ext. Name:</label>
                    <input type="text" name="ext" class="form-control form-control-sm" maxlength="10">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Gender:</label>
                    <select name="gender" class="form-select form-select-sm">
                        <option value="">Select</option>
                        @foreach ($genders as $gender)
                            <option value="{{ $gender->genderstat_name }}">{{ $gender->genderstat_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Civil Status:</label>
                    <select name="civil_status" class="form-select form-select-sm">
                        <option value="">Select</option>
                        @foreach ($civilStatuses as $status)
                            <option value="{{ $status->cvlstat_name }}">{{ $status->cvlstat_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Birthday:</label>
                    <input type="date" name="bday" class="form-control form-control-sm">
                    <small class="text-muted">Saved: <span data-show="birthday"></span></small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Birth Place:</label>
                    <input type="text" name="pbirth" class="form-control form-control-sm" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mobile No.:</label>
                    <input type="text" name="contact" class="form-control form-control-sm" maxlength="20">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email Address:</label>
                    <input type="email" name="email" class="form-control form-control-sm" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Religion:</label>
                    <input type="text" name="religion" class="form-control form-control-sm" maxlength="255">
                </div>
            </div>

            {{-- Address: shared fields (x-employee.address-fields, public/js/employee/address.js) --}}
            <h6 class="fw-bold text-success mb-2 mt-3 pt-2 border-top">Address</h6>
            <x-employee.address-fields :regions="$regions" />

            <h6 class="fw-bold text-success mb-2 mt-3 pt-2 border-top">Family Information</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Father's Name:</label>
                    <input type="text" name="stud_father" class="form-control form-control-sm" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mother's Name:</label>
                    <input type="text" name="stud_mother" class="form-control form-control-sm" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Guardian's Name:</label>
                    <input type="text" name="stud_guardian" class="form-control form-control-sm" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Monthly Income:</label>
                    <input type="text" name="monthly_income" class="form-control form-control-sm" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Guardian Contact No.:</label>
                    <input type="text" name="guardian_contact" class="form-control form-control-sm" maxlength="20">
                </div>
            </div>

            <h6 class="fw-bold text-success mb-2 mt-3 pt-2 border-top">Other Information</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Last School Attended:</label>
                    <input type="text" name="lstsch_attended" class="form-control form-control-sm" maxlength="255">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Last S.Y. Attended:</label>
                    <input type="text" name="lst_sch_attended_year" class="form-control form-control-sm" maxlength="20">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Last University Attended:</label>
                    <input type="text" name="suc_lst_attended" class="form-control form-control-sm" maxlength="255">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date of Admission:</label>
                    <input type="text" class="form-control form-control-sm" data-show="date_admission" readonly>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="studentHistoryTab" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>StudentID</th>
                            <th>School Year</th>
                            <th>Semester</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Section</th>
                            <th>Campus</th>
                        </tr>
                    </thead>
                    <tbody class="js-history-rows"></tbody>
                </table>
            </div>
        </div>
    </div>
</x-employee.form-modal>
