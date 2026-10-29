/**
 * Shared by the admission list pages (applicants.js, examinees.js, results.js): search form, the shared
 * modals (View Data with address, Schedule, Documents, Exam Result) and the Delete confirmation.
 * Uses CissCrud (public/js/employee/crud.js).
 */
window.AdmissionUi = (function () {
    'use strict';

    // Current values of the search form, sent with every table load
    function searchFilters() {
        const form = document.getElementById('admissionSearchForm');
        return {
            year: form.elements.year.value,
            campus: form.elements.campus.value,
            strand: form.elements.strand.value,
        };
    }

    // Fill read-only fields marked data-show="field" inside a modal
    function showFields($modal, row) {
        $modal.find('[data-show]').each(function () {
            this.value = row[this.dataset.show] || this.dataset.empty || '';
        });
    }

    function confirmThen(options, onConfirm) {
        Swal.fire(Object.assign({
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
        }, options)).then((result) => {
            if (result.isConfirmed) onConfirm();
        });
    }

    // Document modal: image, or a link for PDFs, or "No document uploaded"
    function openDocument(title, url, note) {
        const $modal = $('#documentModal');
        const isPdf = url && /\.pdf$/i.test(url);

        $modal.find('.modal-title').text(title);
        $modal.find('.js-document-note').text(note || '');
        $modal.find('.js-document-image').toggleClass('d-none', !url || isPdf).attr('src', url && !isPdf ? url : '');
        $modal.find('.js-document-link').toggleClass('d-none', !isPdf).attr('href', isPdf ? url : '#');
        $modal.find('.js-document-empty').toggleClass('d-none', !!url);
        $modal.modal('show');
    }

    // ---------- Exam result preview (raw score -> stanine / remark; the server computes the saved values the same way) ----------
    function previewResult(input) {
        const stanines = JSON.parse(input.dataset.stanines);
        const remarks = JSON.parse(input.dataset.remarks);
        const passingScore = parseInt(input.dataset.passingScore, 10);
        const score = parseInt(input.value, 10);

        let stanine = null;
        if (score >= 1) {
            const bound = Object.keys(stanines).map(Number).sort((a, b) => a - b).find((max) => score <= max);
            stanine = bound ? stanines[bound] : null;
        }

        let remark = '';
        if (score >= 1) {
            remark = passingScore ? (score >= passingScore ? 'Qualified' : 'Failed') : (stanine ? remarks[stanine] : '');
        }

        const $modal = $(input).closest('.modal');
        $modal.find('.js-stanine').val(stanine || '');
        $modal.find('.js-remark').val(remark);
        $modal.find('.js-qualifier-group').toggleClass('d-none', remark !== 'Failed');
    }

    $(document).on('input', '.js-raw-score', function () {
        previewResult(this);
    });

    // ---------- Address (View Data modal): same ids and flow as the apply form (public/js/portal/apply.js) ----------
    // #region > #province > #city > #barangay (select2bs4) fill the hidden *_name fields; #viewdatastudHnum and
    // #zipcode complete #viewdatastudAddress. Lists come from the employee.locations.* routes (#applicantAddress data-*).
    const Address = (function () {
        function urlFor(key, id) {
            return $('#applicantAddress').data(key).replace('__ID__', encodeURIComponent(id));
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
        $(document).on('change', '#applicantAddress #region', function () {
            $('#region_name').val(selectedName(this));
            $('#province_name, #city_name, #brgy_name, #zipcode').val('');
            resetSelect('#province', 'Select Province');
            resetSelect('#city', 'Select City');
            resetSelect('#barangay', 'Select Barangay');
            if (this.value) loadProvinces(this.value);
            updateAddress();
        });

        $(document).on('change', '#applicantAddress #province', function () {
            $('#province_name').val(selectedName(this));
            $('#city_name, #brgy_name, #zipcode').val('');
            resetSelect('#city', 'Select City');
            resetSelect('#barangay', 'Select Barangay');
            if (this.value) loadCities(this.value);
            updateAddress();
        });

        $(document).on('change', '#applicantAddress #city', function () {
            $('#city_name').val(selectedName(this));
            $('#zipcode').val($(this).find(':selected').attr('data-zip') || '');
            $('#brgy_name').val('');
            resetSelect('#barangay', 'Select Barangay');
            if (this.value) loadBarangays(this.value);
            updateAddress();
        });

        $(document).on('change', '#applicantAddress #barangay', function () {
            $('#brgy_name').val(selectedName(this));
            updateAddress();
        });

        $(document).on('input', '#applicantAddress #viewdatastudHnum', updateAddress);

        // ----- select2bs4 on page load (like the apply form); dropdownParent keeps it usable inside the modal -----
        document.addEventListener('livewire:navigated', function () {
            const $modal = $('#applicantInfoModal');
            if ($modal.length) {
                $modal.find('.select2bs4').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $modal });
            }
        });

        // ----- Show an applicant's saved address: pick each level by its saved name, then load the next list -----
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

    // Row actions used by both pages (spread into a page's CissCrud actions)
    const actions = {
        view(row) {
            const { $modal, form } = CissCrud.openFor('#applicantInfoModal', row);
            showFields($modal, row);
            CissCrud.fill(form, row, [
                'fname', 'mname', 'lname', 'ext', 'gender', 'bday', 'civil_status',
                'region', 'province', 'city', 'brgy', 'hnum', 'zcode', 'address',
            ]);
            Address.show(row);
            $modal.modal('show');
        },

        result(row) {
            const { $modal, form } = CissCrud.openFor('#resultModal', row);
            showFields($modal, row);
            form.elements.raw_score.value = row.raw_score ?? '';
            if (form.elements.second_qualifier) {
                form.elements.second_qualifier.checked = String(row.qualifier) === '2';
            }
            previewResult(form.elements.raw_score);
            $modal.modal('show');
        },

        schedule(row) {
            const { $modal, form } = CissCrud.openFor('#scheduleModal', row);
            showFields($modal, row);

            // Full schedules can't be picked, except the one the applicant already has
            $(form.elements.dateID).find('option[data-full="1"]').each(function () {
                this.disabled = String(this.value) !== String(row.dateID);
            });
            form.elements.dateID.value = row.dateID || '';
            form.elements.venue.value = row.venue || '';
            $modal.modal('show');
        },

        schoolId(row) {
            openDocument('School ID / Valid ID - ' + row.display_name, row.school_id_url);
        },

        reportCard(row) {
            openDocument('Report Card / TOR - ' + row.display_name, row.report_card_url);
        },

        proof(row) {
            openDocument('Proof/Evidence - ' + row.display_name, row.proof_url, row.proof_type ? 'Type: ' + row.proof_type : '');
        },

        cancel(row) {
            confirmThen({
                title: 'Remove this applicant?',
                html: CissCrud.escapeHtml(row.display_name) + ' will be removed from the list.',
                confirmButtonText: 'Yes, remove',
                confirmButtonColor: '#dc3545',
            }, () => CissCrud.send('cancelUrl', row.id));
        },
    };

    // Search: reload the table with the new filters (no page reload)
    $(document).on('submit', '#admissionSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    return { searchFilters, showFields, confirmThen, openDocument, actions };
})();
