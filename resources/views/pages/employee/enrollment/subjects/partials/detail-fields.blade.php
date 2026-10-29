{{-- The subject's details, in the Add and Edit forms (everything but the code, college and department) --}}
<div class="col-md-6">
    <label class="form-label fw-semibold">Subject Name: <span class="text-danger">*</span></label>
    <input type="text" name="sub_name" class="form-control form-control-sm" maxlength="50" required>
</div>
<div class="col-md-6">
    <label class="form-label fw-semibold">Subject Description: <span class="text-danger">*</span></label>
    <input type="text" name="sub_title" class="form-control form-control-sm" maxlength="255" required>
</div>

<div class="col-md-2">
    <label class="form-label fw-semibold">Lec. Unit: <span class="text-danger">*</span></label>
    <input type="number" name="sublecredit" class="form-control form-control-sm" min="0" max="30" step="0.5" required>
</div>
<div class="col-md-2">
    <label class="form-label fw-semibold">Lab. Unit: <span class="text-danger">*</span></label>
    <input type="number" name="sublabcredit" class="form-control form-control-sm" min="0" max="30" step="0.5" required>
</div>
<div class="col-md-2">
    <label class="form-label fw-semibold">Total Unit: <span class="text-danger">*</span></label>
    <input type="number" name="sub_unit" class="form-control form-control-sm" min="0" max="30" step="0.5" required>
</div>
<div class="col-md-2">
    <label class="form-label fw-semibold">No. of Weeks: <span class="text-danger">*</span></label>
    <input type="number" name="subjweeks" class="form-control form-control-sm" min="1" max="52" required>
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Hours/Weeks: <span class="text-danger">*</span></label>
    <input type="number" name="subjconthrs" class="form-control form-control-sm" min="0" max="100" step="0.5" required>
</div>

<div class="col-md-4">
    <label class="form-label fw-semibold">Level: <span class="text-danger">*</span></label>
    <select class="form-control form-control-sm" name="subjlev" required>
        <option value="" selected disabled>--Select--</option>
        @foreach ($levels as $level)
            <option value="{{ $level->id }}">{{ $level->studLevel }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Delivery Mode: <span class="text-danger">*</span></label>
    <select class="form-control form-control-sm" name="subdelmod" required>
        <option value="" selected disabled>--Select--</option>
        @foreach ($deliveryModes as $mode)
            <option value="{{ $mode->id }}">{{ $mode->delmode }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-4">
    <label class="form-label fw-semibold">Academic Type: <span class="text-danger">*</span></label>
    <select class="form-control form-control-sm" name="subacadtype" required>
        <option value="" selected disabled>--Select--</option>
        @foreach ($academicTypes as $type)
            <option value="{{ $type->id }}">{{ $type->acadtype_name }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-6">
    <label class="form-label fw-semibold">Subject Pre-requisite:</label>
    <input type="text" name="subjprereq" class="form-control form-control-sm" maxlength="255">
</div>
<div class="col-md-6">
    <label class="form-label fw-semibold">Subject Co-requisite:</label>
    <input type="text" name="subjcoreq" class="form-control form-control-sm" maxlength="255">
</div>
