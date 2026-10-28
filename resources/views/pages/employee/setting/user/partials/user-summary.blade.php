{{-- Read-only name (and optionally user level) of the user a modal is editing; filled by users.js --}}
<div class="form-group">
    <div class="row">
        <div class="col-md-12">
            <label>Name:</label>
            <input type="text" class="form-control form-control-sm js-user-name" readonly>
        </div>
    </div>
</div>

@if ($withRole ?? true)
    <div class="form-group mt-3">
        <div class="row">
            <div class="col-md-12">
                <label>User Level:</label>
                @include('pages.employee.setting.user.partials.role-select')
            </div>
        </div>
    </div>
@endif
