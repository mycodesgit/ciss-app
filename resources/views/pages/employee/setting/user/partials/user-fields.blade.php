{{-- Fields shared by the Add and Edit user forms. $withPassword adds the password field (Add only). --}}
<div class="form-group">
    <div class="row g-3">
        <div class="col-md-4">
            <label>First Name: <span class="text-danger">*</span></label>
            <input type="text" name="fname" placeholder="Enter First Name" class="form-control form-control-sm js-capitalize" required>
        </div>
        <div class="col-md-4">
            <label>Middle Name: @if ($withPassword ?? false)<span class="text-danger">*</span>@endif</label>
            <input type="text" name="mname" placeholder="Enter Middle Name" class="form-control form-control-sm js-capitalize" @required($withPassword ?? false)>
        </div>
        <div class="col-md-4">
            <label>Last Name: <span class="text-danger">*</span></label>
            <input type="text" name="lname" placeholder="Enter Last Name" class="form-control form-control-sm js-capitalize" required>
        </div>
    </div>
</div>

<div class="form-group mt-3">
    <div class="row g-3">
        <div class="col-md-4">
            <label>Ext.: </label>
            <select name="ext" class="form-control form-control-sm">
                <option value="">N/A</option>
                @foreach ($extensions as $extension)
                    <option value="{{ $extension }}">{{ $extension }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label>Email: <span class="text-danger">*</span></label>
            <input type="email" name="email" placeholder="Enter Email" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-4">
            <label>Campus: <span class="text-danger">*</span></label>
            <select name="campus" class="form-control form-control-sm" required>
                <option value="" disabled selected>Select</option>
                @foreach ($campuses as $code => $name)
                    <option value="{{ $code }}">{{ str_replace(' Campus', '', $name) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="form-group mt-3">
    <div class="row g-3">
        <div class="col-md-8">
            <label>Department: <span class="text-danger">*</span></label>
            <select name="dept" class="form-control form-control-sm" required>
                <option value="" disabled selected>Select</option>
                @foreach ($departments as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label>User Level: <span class="text-danger">*</span></label>
            @include('pages.employee.setting.user.partials.role-select', ['name' => 'role'])
        </div>
    </div>
</div>

@if ($withPassword ?? false)
    <div class="form-group mt-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label>Password: <span class="text-danger">*</span></label>
                <input type="password" name="password" placeholder="At least 8 characters" class="form-control form-control-sm" minlength="8" autocomplete="new-password" required>
            </div>
        </div>
    </div>
@endif
