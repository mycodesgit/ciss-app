/**
 * Address fields of employee forms (x-employee.address-fields), same ids and flow as the apply form
 * (public/js/portal/apply.js). Used by the admission View Data modal and Enrollment > Student Information.
 * Loaded once from employee-assets.
 *
 * #region > #province > #city > #barangay (select2bs4) fill the hidden *_name fields; #viewdatastudHnum and
 * #zipcode complete #viewdatastudAddress. Lists come from the employee.locations.* routes (PortalLocationController),
 * read from the .js-address container's data-* URLs. Only one address block is on a page.
 *
 * CissAddress.show(row) shows a saved address (row.region, row.province, row.city, row.brgy are names).
 */
window.CissAddress = (function () {
    'use strict';

    function urlFor(key, id) {
        return $('.js-address').data(key).replace('__ID__', encodeURIComponent(id));
    }

    function rowsFrom(data) {
        return Array.isArray(data) ? data : (data && Array.isArray(data.data) ? data.data : []);
    }

    function resetSelect(selector, placeholder) {
        $(selector).html('<option value="">' + placeholder + '</option>').trigger('change.select2');
    }

    function fillSelect(selector, placeholder, rows, buildOption) {
        const $select = $(selector).html('<option value="">' + placeholder + '</option>');
        rows.forEach((row) => $select.append(buildOption(row)));
        $select.trigger('change.select2');
    }

    function loadProvinces(regionId) {
        return $.get(urlFor('provincesUrl', regionId)).done(function (data) {
            fillSelect('#province', 'Select Province', rowsFrom(data), (p) => $('<option>').val(p.province_id).attr('data-name', p.name).text(p.name));
        });
    }

    function loadCities(provinceId) {
        return $.get(urlFor('citiesUrl', provinceId)).done(function (data) {
            fillSelect('#city', 'Select City', rowsFrom(data), (c) => $('<option>').val(c.city_id).attr('data-name', c.name).attr('data-zip', c.zip_code || '').text(c.name));
        });
    }

    function loadBarangays(cityId) {
        return $.get(urlFor('barangaysUrl', cityId)).done(function (data) {
            fillSelect('#barangay', 'Select Barangay', rowsFrom(data), (b) => $('<option>').val(b.id).attr('data-name', b.name).text(b.name));
        });
    }

    function selectedName(select) {
        return $(select).find(':selected').attr('data-name') || '';
    }

    // Same as the apply form: house no., barangay, city, province, region, zip code
    function updateAddress() {
        const fullAddress = [
            $('#viewdatastudHnum').val(),
            $('#brgy_name').val(),
            $('#city_name').val(),
            $('#province_name').val(),
            $('#region_name').val(),
            $('#zipcode').val(),
        ].filter(Boolean).join(', ');

        $('#viewdatastudAddress').val(fullAddress);
    }

    // ----- User changes (bound once; select2 fires "change" when an option is picked) -----
    $(document).on('change', '.js-address #region', function () {
        $('#region_name').val(selectedName(this));
        $('#province_name, #city_name, #brgy_name, #zipcode').val('');
        resetSelect('#province', 'Select Province');
        resetSelect('#city', 'Select City');
        resetSelect('#barangay', 'Select Barangay');
        if (this.value) loadProvinces(this.value);
        updateAddress();
    });

    $(document).on('change', '.js-address #province', function () {
        $('#province_name').val(selectedName(this));
        $('#city_name, #brgy_name, #zipcode').val('');
        resetSelect('#city', 'Select City');
        resetSelect('#barangay', 'Select Barangay');
        if (this.value) loadCities(this.value);
        updateAddress();
    });

    $(document).on('change', '.js-address #city', function () {
        $('#city_name').val(selectedName(this));
        $('#zipcode').val($(this).find(':selected').attr('data-zip') || '');
        $('#brgy_name').val('');
        resetSelect('#barangay', 'Select Barangay');
        if (this.value) loadBarangays(this.value);
        updateAddress();
    });

    $(document).on('change', '.js-address #barangay', function () {
        $('#brgy_name').val(selectedName(this));
        updateAddress();
    });

    $(document).on('input', '.js-address #viewdatastudHnum', updateAddress);

    // ----- select2bs4 on every page with an address block; dropdownParent keeps it usable inside a modal -----
    document.addEventListener('livewire:navigated', function () {
        const $address = $('.js-address');
        if (!$address.length) return;

        const $modal = $address.closest('.modal');
        $address.find('.select2bs4').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $modal.length ? $modal : $(document.body) });
    });

    // ----- Show a saved address: pick each level by its saved name, then load the next list -----
    function sameName(a, b) {
        const normalize = (value) => String(value || '').trim().replace(/\s+/g, ' ').toUpperCase();
        return normalize(a) !== '' && normalize(a) === normalize(b);
    }

    // Selects the option whose data-name matches; updates select2 without firing the change handlers
    function pickByName(selector, name) {
        const option = $(selector).find('option').toArray().find((o) => sameName(o.getAttribute('data-name'), name));
        $(selector).val(option ? option.value : '').trigger('change.select2');
        return option ? option.value : '';
    }

    function show(row) {
        resetSelect('#province', 'Select Province');
        resetSelect('#city', 'Select City');
        resetSelect('#barangay', 'Select Barangay');

        const regionId = pickByName('#region', row.region);
        if (!regionId) return;

        loadProvinces(regionId).done(function () {
            const provinceId = pickByName('#province', row.province);
            if (!provinceId) return;

            loadCities(provinceId).done(function () {
                const cityId = pickByName('#city', row.city);
                if (!cityId) return;

                loadBarangays(cityId).done(() => pickByName('#barangay', row.brgy));
            });
        });
    }

    return { show };
})();
