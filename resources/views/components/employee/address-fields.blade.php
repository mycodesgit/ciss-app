{{--
    Address fields: same markup and ids as the apply form (portal/apply.blade.php): select2bs4 #region > #province >
    #city > #barangay fill the hidden name fields (region, province, city, brgy are saved as names); House No. and
    Zip Code complete the Address line. Lists come from the employee.locations.* routes (PortalLocationController).
    Driven by public/js/employee/address.js (CissAddress). Only one address block is on a page (fixed ids).
    Usage: x-employee.address-fields with :regions="$regions" (region_id, name)
--}}
@props(['regions'])

<div class="js-address"
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
