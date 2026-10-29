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
    </div>

    {{--
        Address: same markup and ids as the apply form (portal/apply.blade.php): select2bs4 #region > #province >
        #city > #barangay fill the hidden name fields; House No. and Zip Code complete the Address line.
        Lists come from the employee.locations.* routes (auth:web). Only one View Data modal is on a page.
    --}}
    <h6 class="fw-bold text-success mb-2 mt-3 pt-2 border-top">Address <small class="text-muted fw-normal">(editable)</small></h6>
    <div id="applicantAddress"
        data-provinces-url="{{ route('employee.locations.provinces', '__ID__') }}"
        data-cities-url="{{ route('employee.locations.cities', '__ID__') }}"
        data-barangays-url="{{ route('employee.locations.barangays', '__ID__') }}">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Region:</label>
                <select id="region" class="form-control form-control-sm select2bs4">
                    <option value="">Select Region</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->region_id }}" data-name="{{ $region->name }}">{{ $region->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" id="region_name" name="region">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Province:</label>
                <select id="province" class="form-control form-control-sm select2bs4">
                    <option value="">Select Province</option>
                </select>
                <input type="hidden" id="province_name" name="province">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">City/Municipality:</label>
                <select id="city" class="form-control form-control-sm select2bs4">
                    <option value="">Select City</option>
                </select>
                <input type="hidden" id="city_name" name="city">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Barangay:</label>
                <select id="barangay" class="form-control form-control-sm select2bs4" style="text-transform: uppercase;">
                    <option value="">Select Barangay</option>
                </select>
                <input type="hidden" id="brgy_name" name="brgy">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">House No. / Block / Purok:</label>
                <input type="text" name="hnum" id="viewdatastudHnum" class="form-control form-control-sm" placeholder="House No. / Block / Purok" style="text-transform: uppercase;">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Zipcode:</label>
                <input type="text" name="zcode" id="zipcode" class="form-control form-control-sm" readonly placeholder="Zip Code" style="background-color: #ddd !important; border: 1px solid #aaa;">
            </div>
            <div class="col-md-12">
                <label class="form-label fw-semibold">Address: <span class="text-danger">*</span></label>
                <input type="text" name="address" id="viewdatastudAddress" class="form-control form-control-sm" readonly required style="background-color: #ddd !important; border: 1px solid #aaa;">
                <small class="text-muted">Filled automatically from the fields above.</small>
            </div>
        </div>
    </div>
</x-employee.form-modal>
