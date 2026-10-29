@extends('layouts.master_portal')

@section('title')
    CISS - Admission Apply Portal
@endsection

@section('workspace')
    <div class="row">
        <div class="col-12">
            <div class="mb-6">
                {{-- <h1 class="fs-5 mb-4 d-none d-md-block">Dashboard</h1> --}}

                @if ($isAdmissionOpen)
                    <div class="card" style=" background-color: #e9ecef; margin-top: -10px">
                        <div class="card-body">
                            <ol class="breadcrumb" style="margin-bottom: -3px;">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('portal.admission') }}" wire:navigate class="btn btn-success btn-sm text-light">
                                        <i class="fas fa-home"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active mt-1">Apply Admission</li>
                            </ol>
                        </div>
                    </div>
                    <div class="row g-3 mb-3 mt-1">
                        <div class="col-md-12">
                            @if (!$isWithinOfficeHours)
                                <img src="{{ asset('uilibs/images/limit-has-been-reached.jpg') }}" width="100%" class="img-responsive">
                            @elseif ($isDailyLimitReached)
                                <div class="card card-animate shadow-sm rounded-4 my-4 overflow-hidden">
                                    <div class="card-body p-4 text-center">
                                        <!-- Status Icon -->
                                        <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3 p-3" style="width: 64px; height: 64px;">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>

                                        <!-- Headline -->
                                        <h4 class="fw-bold text-dark mb-2">Daily Limit Reached</h4>

                                        <!-- Detailed Text -->
                                        <p class="text-muted mb-3 mx-auto" style="max-width: 480px;">
                                            We have reached the maximum limit of <strong>3,000 registrations</strong> for today. Please check back tomorrow to submit your application.
                                        </p>

                                        <!-- Status Badge -->
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
                                            <i class="fas fa-clock me-1"></i> Registration Opens Tomorrow
                                        </span>
                                    </div>
                                </div>
                            @else
                                <form method="post" action="{{ route('portal.apply.store') }}" enctype="multipart/form-data" id="admissionApply"
                                    data-programs-url="{{ route('portal.apply.programs') }}"
                                    data-schedules-url="{{ route('portal.apply.schedules') }}"
                                    data-provinces-url="{{ route('portal.locations.provinces', '__ID__') }}"
                                    data-cities-url="{{ route('portal.locations.cities', '__ID__') }}"
                                    data-barangays-url="{{ route('portal.locations.barangays', '__ID__') }}">
                                    {{ csrf_field() }}

                                    <div id="card-1">
                                        <p>
                                            @if(Session::has('success'))
                                                <div class="alert alert-success" id="alert">{{ Session::get('success')}} {{ Session::get('admission_id')}}</div>
                                            @elseif (Session::has('fail'))
                                                <div class="alert alert-danger" id="alert">{{Session::get('fail')}}</div>
                                            @endif
                                        </p>
                                        <div class="card rounded-4 mb-3">
                                            <div class="card-header">
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title text-dark">
                                                    <p>In relation to the Data Privacy Act of 2012, I am willing to share the information for Admission Testing Application purposes only and to hold the University absolutely free from liability that may arise resulting from he sharing of the information to be gathered.<br><br>
                                                        <div class="icheck-success col-md-12">
                                                            <input type="checkbox" id="checkboxPrimaryAgree1" name="studagree" value="Yes I agree">
                                                            <label for="checkboxPrimaryAgree1">
                                                                <span style="font-weight: bold;">I agree</span>
                                                            </label>
                                                        </div>
                                                    </p>
                                                </h6>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                                            <input type="email" class="form-control form-control-sm rounded-2" placeholder="e.g john@gmail.com" name="email" id="email" value="{{old('email')}}">
                                                        </div>
                                                        <div id="verification-message" style="display: none; color: blue; margin-top: 5px;">
                                                            Wait, email is verifying...
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="card-2" style="display: none;">
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title m-0"><i class="ti ti-user"></i> Personal Information</h5>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Last Name: <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control form-control-sm rounded-2" placeholder="Enter Last Name" oninput="this.value = this.value.toUpperCase()" value="{{old('lastname')}}" name="lastname">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">First Name: <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control form-control-sm rounded-2" placeholder="Enter First Name" oninput="this.value = this.value.toUpperCase()"  name="firstname" value="{{old('firstname')}}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Middle Name: </label>
                                                            <input type="text" class="form-control form-control-sm rounded-2" placeholder="Enter Middle Name" oninput="this.value = this.value.toUpperCase()" value="{{old('mname')}}" name="mname">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Name Extension: (Jr., Sr., II, III, IV)</label>
                                                            <select class="form-control form-control-sm rounded-2" name="ext">
                                                                <option disabled selected> --Select-- </option>
                                                                <option value="">N/A</option>
                                                                <option value="Jr." @if (old('ext') == "Jr.") {{ 'selected' }} @endif>Jr.</option>
                                                                <option value="Sr." @if (old('ext') == "Sr.") {{ 'selected' }} @endif>Sr.</option>
                                                                <option value="III" @if (old('ext') == "III") {{ 'selected' }} @endif>III</option>
                                                                <option value="IV" @if (old('ext') == "IV") {{ 'selected' }} @endif>IV</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Birthday: <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control form-control-sm rounded-2" name="bday" id="bday">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Age: <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control form-control-sm rounded-2" name="age" id="age" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Mobile Number: <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control form-control-sm" placeholder="Enter Mobile Number" name="contact" value="{{old('contact')}}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Civil Status: <span class="text-danger">*</span></label>
                                                            <select class="form-control form-control-sm rounded-2" name="civil_status">
                                                                <option disabled selected>Select</option>
                                                                <option value="Single" @if (old('civil_status') == "Single") {{ 'selected' }} @endif>Single</option>
                                                                <option value="Married" @if (old('civil_status') == "Married") {{ 'selected' }} @endif>Married</option>
                                                                <option value="Divorced" @if (old('civil_status') == "Divorced") {{ 'selected' }} @endif>Divorced</option>
                                                                <option value="Widowed" @if (old('civil_status') == "Widowed") {{ 'selected' }} @endif>Widowed</option>
                                                                <option value="Separated" @if (old('civil_status') == "Separated") {{ 'selected' }} @endif>Separated</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Sex: <span class="text-danger">*</span></label>
                                                            <select class="form-control form-control-sm rounded-2" name="gender" id="genderSelect">
                                                                <option value="">Select</option>
                                                                <option value="Male" @if (old('gender') == "Male") {{ 'selected' }} @endif>Male</option>
                                                                <option value="Female" @if (old('gender') == "Female") {{ 'selected' }} @endif>Female</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Religion: <span class="text-danger">*</span></label>
                                                            <select class="form-control form-control-sm rounded-2" name="religion" id="religionSelect">
                                                                <option value="" disabled selected>Select Religion</option>
                                                                <option value="ROMAN CATHOLIC">ROMAN CATHOLIC</option>
                                                                <option value="CHRISTIAN">CHRISTIAN</option>
                                                                <option value="BAPTIST">BAPTIST</option>
                                                                <option value="METHODIST">METHODIST</option>
                                                                <option value="IGLESIA NI CRISTO">IGLESIA NI CRISTO</option>
                                                                <option value="SEVENTH-DAY ADVENTIST">SEVENTH-DAY ADVENTIST</option>
                                                                <option value="JEHOVAH'S WITNESS">JEHOVAH'S WITNESS</option>
                                                                <option value="ISLAM">ISLAM</option>
                                                                <option value="BUDDHISM">BUDDHISM</option>
                                                                <option value="HINDUISM">HINDUISM</option>
                                                                <option value="JUDAISM">JUDAISM</option>
                                                                <option value="ATHEIST">ATHEIST</option>
                                                                <option value="AGNOSTIC">AGNOSTIC</option>
                                                                <option value="OTHER">OTHER (SPECIFY)</option>
                                                            </select>
                                                            <input type="text" id="otherReligionInput" class="form-control form-control-sm rounded-2 mt-2" placeholder="Please specify religion" style="display: none;"
                                                                oninput="this.value = this.value.toUpperCase()"
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Region: <span class="text-danger">*</span></label>
                                                            <select id="region" class="form-control form-control-sm rounded-2 select2bs4">
                                                                <option value="">Select Region</option>
                                                                @foreach($regions as $region)
                                                                    <option value="{{ $region->region_id }}" data-name="{{ $region->name }}">{{ $region->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type="hidden" id="region_name" name="region">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Province: <span class="text-danger">*</span></label>
                                                            <select id="province" class="form-control form-control-sm rounded-2 select2bs4">
                                                                <option value="">Select Province</option>
                                                            </select>
                                                            <input type="hidden" id="province_name" name="province">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">City/Municipality: <span class="text-danger">*</span></label>
                                                            <select id="city" class="form-control form-control-sm rounded-2 select2bs4">
                                                                <option value="">Select City</option>
                                                            </select>
                                                            <input type="hidden" id="city_name" name="city">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Barangay: <span class="text-danger">*</span></label>
                                                            <select id="barangay" class="form-control form-control-sm rounded-2 select2bs4" style="text-transform: uppercase;">
                                                                <option value="">Select Barangay</option>
                                                            </select>
                                                            <input type="hidden" id="brgy_name" name="brgy">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">House No. / Block / Purok: <span class="text-danger">*</span></label>
                                                            <input type="text" name="hnum" id="viewdatastudHnum" class="form-control form-control-sm rounded-2" placeholder="House No. / Block / Purok" style="text-transform: uppercase;">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Zipcode: <span class="text-danger">*</span></label>
                                                            <input type="text" name="zcode" id="zipcode" class="form-control form-control-sm rounded-2" readonly placeholder="Zip Code" readonly style="background-color: #ddd !important; border: 1px solid #aaa;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Address: <span class="text-danger">*</span></label>
                                                            <input type="text" name="address" class="form-control form-control-sm rounded-2" id="viewdatastudAddress" readonly style="background-color: #ddd !important; border: 1px solid #aaa;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Are you the first college student in your family? <span class="text-danger">*</span></label>
                                                            <div class="icheck-success">
                                                                <input type="radio" id="radioPrimaryQO1" name="qstion1" value="Yes">
                                                                <label for="radioPrimaryQO1">
                                                                    Yes
                                                                </label>
                                                            </div>
                                                            <div class="icheck-success">
                                                                <input type="radio" id="radioPrimaryQO2" name="qstion1" value="No">
                                                                <label for="radioPrimaryQO2">
                                                                    No
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3" id="genderQuestion" style="display: ;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Are you the first <span id="genderPlaceholder">FEMALE</span> student in your family to study in college? <span class="text-danger">*</span></label>
                                                            <div class="icheck-success">
                                                                <input type="radio" id="radioPrimaryQO3" name="qstion2" value="Yes">
                                                                <label for="radioPrimaryQO3">
                                                                    Yes
                                                                </label>
                                                            </div>
                                                            <div class="icheck-success">
                                                                <input type="radio" id="radioPrimaryQO4" name="qstion2" value="No">
                                                                <label for="radioPrimaryQO4">
                                                                    No
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="card-3" style="display: none;">
                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="form-row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Admission Type: <span class="text-danger">*</span></label>
                                                            <select class="form-control form-control-sm form-control-sm rounded-2" name="type" id="admissionType">
                                                                <option value="">Select</option>
                                                                <option value="1" @if (old('type') == 1) {{ 'selected' }} @endif>New</option>
                                                                <option value="2" @if (old('type') == 2) {{ 'selected' }} @endif>Returnee</option>
                                                                <option value="3" @if (old('type') == 3) {{ 'selected' }} @endif>Transferee</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Preferred Campus: <span class="text-danger">*</span></label>
                                                            <select class="form-control form-control-sm rounded-2" name="campus" id="campus">
                                                                <option disabled selected>Select</option>
                                                                @foreach ($campuses as $campus)
                                                                    <option value="{{ $campus->code }}">{{ $campus->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="new-returnee-form" id="newReturneeForm" style="display: ;">
                                            <div class="card rounded-4 mb-3">
                                                <div class="card-body">
                                                    <div class="page-header" style="border-bottom: 1px solid #04401f;">
                                                        <h4>For New Student <span style="font-size: 12pt;color:#ff0000;">(For New Applicant only)</span></h4>
                                                    </div>
                                                    <div class="form-group mt-3">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Full Name School Last Attended: <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control form-control-sm rounded-2" oninput="this.value = this.value.toUpperCase()" name="lstsch_attended" value="{{old('lstsch_attended')}}">
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Strand: <span class="text-danger">*</span></label>
                                                                <select class="level form-control form-control-sm rounded-2" name="strand" style="text-transform: uppercase;">
                                                                    <option value="">Select</option>
                                                                    @foreach ($strands as $strand)
                                                                    <option value="{{ $strand->code }}">{{ $strand->strand }} - {{ $strand->code }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="transferee-form" id="transfereeForm" style="display: none;">
                                            <div class="card rounded-4 mb-3">
                                                <div class="card-body">
                                                    <div class="page-header" style="border-bottom: 1px solid #04401f;">
                                                        <h4>For Transferee <span style="font-size: 12pt;color:#ff0000;">(Input for Transferees only)</span></h4>
                                                    </div>
                                                    <div class="form-group mt-3">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">College/University last attended: <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control form-control-sm rounded-2" oninput="this.value = this.value.toUpperCase()" name="suc_lst_attended">
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Course: <span class="text-danger">*</span></label>
                                                                <select class="form-control form-control-sm rounded-2" name="course" id="courseSelect" style="text-transform: uppercase;">
                                                                    <option value="">Select Course</option>
                                                                    @foreach ($programs as $program)
                                                                        <option value="{{ $program->code }}">{{ $program->program }}</option>
                                                                    @endforeach
                                                                    <option value="OTHER">Other (Specify)</option>
                                                                </select>
                                                                {{-- Gets name="course" from apply.js only when "Other" is chosen --}}
                                                                <input type="text" class="form-control form-control-sm mt-2" id="otherCourseInput" placeholder="Please specify course" style="display: none; text-transform: uppercase;" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="page-header" style="border-bottom: 1px solid #04401f;">
                                                    <h4>Course Preference</h4>
                                                </div>
                                                <div class="form-group mt-3">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Course Preference 1: <span class="text-danger">*</span></label>
                                                            <select class="form-control form-control-sm" name="preference_1" style="text-transform: uppercase;">
                                                                <option value="">Select Course Preference</option>

                                                            </select>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Course Preference 2: <span class="text-danger">*</span></label>
                                                            <select class="form-control form-control-sm" name="preference_2" style="text-transform: uppercase;">
                                                                <option value="">Select Course Preference</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Admission Testing Date: <span class="text-danger">*</span></label>
                                                            <select class="form-control form-control-sm rounded-2" name="d_admissionselect">
                                                                <option value="">Select</option>
                                                            </select>
                                                            <input type="hidden" id="selectedDate" name="d_admission" placeholder="Selected Date">
                                                            <input type="hidden" id="selectedTime" name="time" placeholder="Selected Time">
                                                            <input type="hidden" id="selectedDateTimeID" name="dateID" placeholder="Selected Date Time ID">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="card-4" style="display: none;">
                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title m-0"><i class="ti ti-file"></i> Documents Information</h5>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Parent's Monthly Income: <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control form-control-sm rounded-2" oninput="this.value = this.value.toUpperCase()" name="monthly_income" value="{{old('monthly_income')}}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold">Upload School ID / Valid ID: <span class="text-danger">*</span></label>
                                                            <input type="file" name="studiddoc_image" class="form-control form-control-sm rounded-2" id="fileInput" accept="image/*">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="card rounded-4 mb-3">
                                            <div class="card-body">
                                                <div class="page-header" style="border-bottom: 1px solid #04401f;">
                                                    <h4>Application Requirement <span style="font-size: 12pt;color:#ff0000;"></span></h4>
                                                </div>

                                                <!-- Selection -->
                                                <div class="form-group mt-3">
                                                    <label class="form-label fw-semibold" for="applicantType">Select Applicant Type: <span class="text-danger">*</span></label>
                                                    <select id="applicantType" class="form-control form-control-sm rounded-2">
                                                        <option value="">-- Select an option --</option>
                                                        <option value="grade12">Grade 12 Students (Graduating)</option>
                                                        <option value="shsGrad">Senior High School Graduates</option>
                                                        <option value="apptransferee">Transferees</option>
                                                        <option value="als">ALS Graduates/Passers</option>
                                                        <option value="lifelong">Life-long Learner</option>
                                                    </select>
                                                </div>

                                                <!-- Upload Sections -->
                                                <div class="form-group mt-3 upload-section" id="grade12" style="">
                                                    <label>Upload Grade 12 Requirement <i style="color: red">*</i></label>
                                                    <input type="file" name="grade12File" class="form-control form-control-sm rounded-2 file-input" accept="image/*">
                                                </div>

                                                <div class="form-group mt-3 upload-section" id="shsGrad" style="display:none;">
                                                    <label>Upload SHS Graduate Requirement <i style="color: red">*</i></label>
                                                    <input type="file" name="shsFile" class="form-control form-control-sm rounded-2 file-input" accept="image/*">
                                                </div>

                                                <div class="form-group mt-3 upload-section" id="apptransferee" style="display:none;">
                                                    <label>Upload Transferee Requirement <i style="color: red">*</i></label>
                                                    <input type="file" name="transfereeFile" class="form-control form-control-sm rounded-2 file-input" accept="image/*">
                                                </div>

                                                <div class="form-group mt-3 upload-section" id="als" style="display:none;">
                                                    <label>Upload ALS Requirement <i style="color: red">*</i></label>
                                                    <input type="file" name="alsFile" class="form-control form-control-sm rounded-2 file-input" accept="image/*">
                                                </div>

                                                <div class="form-group mt-3 upload-section" id="lifelong" style="display:none;">
                                                    <label>Upload Life-long Learner Requirement <i style="color: red">*</i></label>
                                                    <input type="file" name="lifelongFile" class="form-control form-control-sm rounded-2 file-input" accept="image/*">
                                                </div>
                                            </div>
                                        </div> --}}

                                        <div class="card rounded-4 mb-4">
                                            <div class="card-body p-4">

                                                <!-- Section Title & Instructions -->
                                                <div class="mb-4">
                                                    <h5 class="fw-bold mb-1 text-dark">
                                                        Proof of Equity Target Documents
                                                        <span class="text-danger">*</span>
                                                    </h5>
                                                    <p class="text-muted small mb-0">Select one category that applies to your parent/legal guardian and upload the corresponding document.</p>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <div class="d-flex flex-column gap-2">

                                                            <!-- 1. 4Ps Members -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary1" name="typefileproofupload" value="4Ps" required>
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">4Ps Members</span>
                                                                    <span class="text-muted small">
                                                                        Upload a 4Ps Certification from MSWD/DSWD showing the household number and beneficiary's name, or a 4Ps Household ID.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 2. Indigenous People -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary2" name="typefileproofupload" value="IPs">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Indigenous People (IPs)</span>
                                                                    <span class="text-muted small">
                                                                        Upload an original IP Certificate from the Municipal or City Indigenous Office with an original signature and your valid ID.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 3. Resident of Armed Conflict Areas -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary3" name="typefileproofupload" value="RACA">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Resident of Armed Conflict Areas</span>
                                                                    <span class="text-muted small">
                                                                        Upload a Certification from DILG with your name.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 4. Parents Who Are Business Owners -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary4" name="typefileproofupload" value="PWABO">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Parents Who Are Business Owners</span>
                                                                    <span class="text-muted small">
                                                                        Upload BIR Form 1701A-2021.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 5. Parents Employed in Gov / Private Agencies -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary5" name="typefileproofupload" value="PEIGPA">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Parents Employed in Government / Private Agencies</span>
                                                                    <span class="text-muted small">
                                                                        Upload BIR Form 2316-2021 or a Certification from HR showing salary and compensation details.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 6. Parents Working Abroad -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary6" name="typefileproofupload" value="PWA">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Parents Working Abroad</span>
                                                                    <span class="text-muted small">
                                                                        Upload an Overseas Employment Contract (OEC).
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 7. Deceased Parents -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary7" name="typefileproofupload" value="Low Income">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Applicant with Deceased Parents</span>
                                                                    <span class="text-muted small">
                                                                        Upload a Barangay Certification confirming both parents are deceased.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 8. Solo/Single Parents -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary8" name="typefileproofupload" value="Solo Parent">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Solo / Single Parents</span>
                                                                    <span class="text-muted small">
                                                                        Upload a Solo Parent ID/Certification from MSWD/DSWD or a Notarized Sworn Statement indicating status (Single, Solo parent, Separated, Widow/er).
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 9. Persons with Disabilities -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary9" name="typefileproofupload" value="PWDs">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Person with Disabilities (PWDs)</span>
                                                                    <span class="text-muted small">
                                                                        • <strong>For applicants:</strong> Upload PWD ID.<br>
                                                                        • <strong>For family members:</strong> Upload a PWD Certificate from DSWD stating relationship to applicant.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 10. Rice/Corn Farmers -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary10" name="typefileproofupload" value="CORCF">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Children of Rice/Corn Farmers</span>
                                                                    <span class="text-muted small">
                                                                        Upload DA Membership Certificate, Association/Cooperative President Certification, or Agrarian Reform Beneficiary (DA/DAR/CAO) Certification.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 11. Coconut Farmers -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary11" name="typefileproofupload" value="COCF">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Children of Coconut Farmers</span>
                                                                    <span class="text-muted small">
                                                                        Upload a Certificate of Membership from local coconut association listing parent/guardian's name.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 12. Fisherfolk -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary12" name="typefileproofupload" value="COF">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Children of Fisherfolk</span>
                                                                    <span class="text-muted small">
                                                                        Upload a Certificate of Membership from an active local fisherfolk association or certification from the Association President.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 13. Tricycle/Jeepney Drivers -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary13" name="typefileproofupload" value="COTJDO">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Children of Tricycle / Jeepney Drivers or Operators</span>
                                                                    <span class="text-muted small">
                                                                        Upload a Certificate of Membership from local TODA listing parent/guardian's name.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                            <!-- 14. Unclassified -->
                                                            <label class="custom-option-card d-flex align-items-start p-3 rounded-3 border">
                                                                <input type="radio" class="form-check-input me-3 mt-1" id="radioPrimary14" name="typefileproofupload" value="UHNFIS">
                                                                <div class="flex-grow-1">
                                                                    <span class="fw-semibold text-dark d-block mb-1">Unclassified (Unemployed, No Fixed Income, Self-Supporting)</span>
                                                                    <span class="text-muted small">
                                                                        Upload a Notarized Sworn Statement indicating parent/guardian name, source of income, and income amount.
                                                                    </span>
                                                                </div>
                                                            </label>

                                                        </div>
                                                    </div>

                                                    <!-- Upload Field Section -->
                                                    <div class="col-12 mt-4 pt-3 border-top">
                                                        <label for="proofdoc_image" class="form-label fw-semibold text-dark">
                                                            Upload Selected Document <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="file" id="proofdoc_image" name="proofdoc_image" class="form-control form-control-lg fs-6" accept="image/*,.pdf" required>
                                                        <div class="form-text">Accepted formats: JPG, PNG, PDF (Max size: 5MB)</div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="progress-section d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
                                        {{-- Navigation & Submit Actions Group --}}
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-light" id="back-btn" style="display: none;">
                                                <i class="ti ti-arrow-back me-1"></i> Back
                                            </button>

                                            <button type="button" class="btn btn-success" id="next-btn" style="display: none;" disabled>
                                                Next <i class="ti ti-player-track-next ms-1"></i>
                                            </button>

                                            {{-- <button type="button" class="btn btn-info" id="ok-btn">OK</button> --}}

                                            <button type="submit" class="btn btn-success" id="submit-btn" style="display: none;">
                                                <i class="ti ti-check me-1"></i> Submit
                                            </button>
                                        </div>

                                        {{-- Progress Indicator & Clear Form Group --}}
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="progress-container d-flex align-items-center gap-2">
                                                <div class="progress" style="width: 120px; height: 10px; background-color: #e9ecef; border-radius: 20px;">
                                                    <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%; border-radius: 20px;" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted text-nowrap fw-semibold" id="progress-text">
                                                    Page 1 of <span id="total-pages"></span>
                                                </small>
                                            </div>

                                            <button type="button" id="clear-form-btn" class="btn btn-link text-decoration-underline p-0 border-0" style="color: #5e5df0;">
                                                Clear form
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        {{-- Top decorative accent bar (Danger/Warning red) --}}
                        <div class="bg-danger" style="height: 6px;"></div>
                        <div class="card-body p-4 p-md-5 text-center">
                            {{-- Status Icon --}}
                            <div class="mb-4">
                                <span class="fa-stack fa-2x">
                                    <i class="fas fa-circle fa-stack-2x text-danger opacity-25"></i>
                                    <i class="fas fa-lock fa-stack-1x text-danger"></i>
                                </span>
                            </div>

                            {{-- Heading & Messages --}}
                            <h2 class="text-danger fw-bold mb-3">Admission Portal is Closed</h2>
                            <h4 class="fw-semibold text-dark mb-3">We are not accepting applications at this time.</h4>
                            <p class="text-muted mb-4 fs-6">
                                The application period for this term has ended or has not yet opened. Please check back later or monitor official announcements for upcoming application schedules.
                            </p>

                            {{-- Info Box (Optional: Display upcoming dates or help info) --}}
                            <div class="bg-light border rounded-3 p-3 mb-4 text-start max-width-500 mx-auto">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary fs-4 me-3"></i>
                                    <div>
                                        <strong class="d-block text-dark">Need Assistance?</strong>
                                        <span class="text-muted small">
                                            If you believe this is an error or have questions regarding your application, please contact the Admission Office.
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('portal.admission') }}" wire:navigate class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Portal Home
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Custom Option Card Styling */
    .custom-option-card {
        background-color: #f8f9fa;
        border-color: #e9ecef !important;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    .custom-option-card:hover {
        background-color: #ffffff;
        border-color: #00c951 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    /* Highlight card when radio is checked */
    .custom-option-card:has(input[type="radio"]:checked) {
        background-color: #f0f7ff;
        border-color: #00c951 !important;
        box-shadow: 0 2px 6px rgba(13, 110, 253, 0.15);
    }

    .custom-option-card input[type="radio"]:checked {
        background-color: #00c951;
        border-color: #00c951;
    }

    /* Keep the space but hide address selects until Select2 has styled them (no jump) */
    .select2bs4:not(.select2-hidden-accessible) {
        visibility: hidden;
    }
    </style>
@endpush

@push('scripts')
    <!-- Apply wizard -->
    <x-script src="js/portal/apply.js" />
@endpush
