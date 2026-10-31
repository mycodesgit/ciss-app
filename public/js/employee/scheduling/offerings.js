/**
 * Scheduling > Subject Offered (resources/views/pages/employee/scheduling/offerings/index.blade.php).
 * Server: SubjectOfferedController. Table, Add / Edit saving and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js). The list response also carries the term's sections for the selects.
 * Units and fees in the forms are a server preview (the server computes the saved values).
 * Add from Curriculum (template campuses only): the server lists a section's curriculum subjects, and when saving
 * reads them again itself; only the section is sent.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const FORM = 'schedOfferingsSearchForm';
    const page = () => document.getElementById('schedOfferings');
    let searched = {};
    let sections = [];

    function termLabel() {
        const semester = document.querySelector('#' + FORM + ' select[name="semester"] option[value="' + searched.semester + '"]');
        return semester ? semester.textContent + ' ' + searched.schlyear + ' (' + searched.campus + ')' : '';
    }

    function fillSections(select, valueKey, selected) {
        CissScheduling.fillSelect(select, sections.map((section) => ({ value: section[valueKey], label: section.name })), '--Select--');
        select.value = selected || '';
    }

    function fillOjt(select, semester) {
        const options = JSON.parse(page().dataset.ojtOptions)[String(semester) === '3' ? 'summer' : 'regular'];
        CissScheduling.fillSelect(select, Object.entries(options).map(([value, label]) => ({ value, label })));
    }

    // Opens the Add / Edit modal; offering = null for Add
    function openForm(modalId, offering) {
        const { $modal, form } = CissCrud.openFor(modalId, offering);

        CissScheduling.copyFilters(form, searched);
        fillSections(form.elements.subSec, 'name', offering && offering.subSec);
        fillOjt(form.elements.isOJT, searched.semester);
        CissScheduling.subjectPicker(form.elements.subCode, page().dataset.subjectsUrl);
        form.elements.lecFee.readOnly = true;
        $(form.elements.fund).find('.js-keep-fund').remove();

        if (offering) {
            CissCrud.fill(form, offering, ['maxstud', 'isType', 'isTemp', 'isOJT', 'itfee', 'lecUnit', 'labUnit', 'subUnit', 'lecFee', 'labFee', 'devFee']);
            CissScheduling.setSubject(form.elements.subCode, offering.subCode, offering.sub_name + ' - ' + offering.sub_title + ' (' + offering.subCode + ')');
            form.elements.lecFee.readOnly = !offering.graduate;

            // An older fund account that is no longer a choice stays as it is unless another is picked
            if (!offering.fund_id && offering.fundAccount) {
                $(form.elements.fund).append(new Option('Keep: ' + offering.fund + ' - ' + offering.fundAccount, 'keep')).find('option:last').addClass('js-keep-fund');
                form.elements.fund.value = 'keep';
            } else {
                form.elements.fund.value = offering.fund_id || '';
            }

            // Show the fees a save would compute now (the saved fund stays)
            CissScheduling.previewFees(form, page().dataset.feesUrl, false);
        } else {
            form.elements.maxstud.value = page().dataset.maxStudents;
            $(form.elements.subCode).val(null).trigger('change');
        }

        $modal.find('.js-modal-term').text(termLabel());
        $modal.modal('show');
    }

    const money = (value) => value == null || value === '' ? '0' : value;

    CissCrud.register({
        pageId: 'schedOfferings',
        table: '#schedOfferingsTable',
        order: [[1, 'asc'], [2, 'asc']],
        ajaxData: () => (searched = CissScheduling.filters(FORM)),
        columns: [
            { data: 'subCode', render: text },
            { data: 'subSec', render: text },
            { data: 'sub_name', render: (name, type, row) => type === 'display' ? CissCrud.escapeHtml(name) + '<small class="text-muted d-block">' + CissCrud.escapeHtml(row.sub_title) + '</small>' : name },
            { data: 'lecUnit', render: text },
            { data: 'labUnit', render: text },
            { data: 'subUnit', render: text },
            { data: 'maxstud', render: text },
            { data: 'lecFee', render: (fee) => CissCrud.escapeHtml(money(fee)) },
            { data: 'labFee', render: (fee) => CissCrud.escapeHtml(money(fee)) },
            { data: 'devFee', render: (fee) => CissCrud.escapeHtml(money(fee)) },
            { data: 'isType', render: (type) => CissCrud.escapeHtml(type || 'No') },
            { data: 'fundAccount', render: (account) => CissCrud.escapeHtml(account || 'No Account') },
            { data: 'itfee', render: text },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([
                    { action: 'edit', icon: 'fas fa-pen', label: 'Edit' },
                    { action: 'destroy', icon: 'fas fa-trash', label: 'Delete', color: '#dc3545' },
                ]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No subjects offered in this campus and term yet.' },
            drawCallback: function () {
                sections = (this.api().ajax.json() || {}).sections || [];
                const templates = JSON.parse(page().dataset.templateCampuses);
                $('#schedOfferings .js-template').toggleClass('d-none', !templates.includes(searched.campus));
                $('#schedOfferings .js-term-shown').text('- ' + termLabel());
            },
        },
        actions: {
            edit(offering) {
                openForm('#editOfferingModal', offering);
            },

            destroy(offering) {
                Swal.fire({
                    title: 'Delete this subject offered?',
                    text: offering.subCode + ' of ' + offering.subSec + ' will be removed. A subject with enrolled students or a class schedule cannot be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) CissCrud.send('destroyUrl', offering.id, 'DELETE');
                });
            },
        },
    });

    $(document).on('submit', '#' + FORM, function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    $(document).on('click', '#schedOfferings .js-add', function () {
        openForm('#addOfferingModal', null);
    });

    // Fee preview: a new subject (also picks its suggested fund), OJT/Thesis, or a typed graduate lecture fee
    $(document).on('change', '#schedOfferings .js-subject', function () {
        CissScheduling.previewFees(this.form, page().dataset.feesUrl, true);
    });
    $(document).on('change', '#schedOfferings form .js-fee-input', function () {
        CissScheduling.previewFees(this.form, page().dataset.feesUrl, false);
    });

    // ---------- Add from Curriculum ----------

    function templateParams() {
        return {
            campus: searched.campus,
            schlyear: searched.schlyear,
            semester: searched.semester,
            class: $('#templateModal .js-template-section').val(),
        };
    }

    function renderTemplate(rows) {
        const $body = $('#templateModal .js-template-rows');
        const fresh = rows.filter((row) => !row.offered).length;

        $body.html(rows.length ? rows.map((row) => '<tr' + (row.offered ? ' class="text-muted"' : '') + '>' +
            '<td>' + CissCrud.escapeHtml(row.subCode) + '</td>' +
            '<td>' + CissCrud.escapeHtml(row.sub_name) + '<small class="d-block">' + CissCrud.escapeHtml(row.sub_title) + '</small></td>' +
            '<td>' + CissCrud.escapeHtml(row.subUnit) + '</td>' +
            '<td>' + CissCrud.escapeHtml([row.lecFee, row.labFee, row.devFee].map(money).join(' / ')) + '</td>' +
            '<td>' + CissCrud.escapeHtml(row.fundAccount || 'No Account') + '</td>' +
            '<td>' + CissCrud.escapeHtml(row.isOJT || 'No') + '</td>' +
            '<td>' + CissCrud.escapeHtml(row.isType || 'No') + '</td>' +
            '<td>' + (row.offered ? CissCrud.badge('bg-secondary-subtle text-secondary', 'Already offered') : CissCrud.badge('bg-success-subtle text-success', 'New')) + '</td>' +
            '</tr>').join('') : '<tr><td colspan="8" class="text-center text-muted">No curriculum subjects for this section\'s year level and semester.</td></tr>');

        $('#templateModal .js-template-note').text(rows.length ? fresh + ' new subject(s) will be offered.' : '');
        $('#templateModal .js-template-save').prop('disabled', fresh === 0);
    }

    $(document).on('show.bs.modal', '#templateModal', function (e) {
        if (e.target !== this) return;
        fillSections($(this).find('.js-template-section')[0], 'id');
        $(this).find('.js-modal-term').text('- ' + termLabel());
        $(this).find('.js-template-rows').html('<tr><td colspan="8" class="text-center text-muted">Pick a section, then click Show Curriculum Subjects.</td></tr>');
        $(this).find('.js-template-note').text('');
        $(this).find('.js-template-save').prop('disabled', true);
    });

    $(document).on('change', '#templateModal .js-template-section', function () {
        $('#templateModal .js-template-save').prop('disabled', true);
    });

    $(document).on('click', '#templateModal .js-template-show', function () {
        const params = templateParams();
        if (!params.class) return toastr.warning('Pick a section first.');

        $.get(page().dataset.templateUrl, params)
            .done((response) => renderTemplate(response.data))
            .fail((xhr) => toastr.error(((xhr.responseJSON || {}).errors ? Object.values(xhr.responseJSON.errors)[0][0] : (xhr.responseJSON || {}).message) || 'Could not read the curriculum.'));
    });

    $(document).on('click', '#templateModal .js-template-save', function () {
        const $button = $(this).prop('disabled', true);

        $.ajax({
            url: page().dataset.templateStoreUrl,
            type: 'POST',
            data: templateParams(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
            .done(function (response) {
                toastr.success(response.message);
                $('#templateModal').modal('hide');
                CissCrud.reload();
            })
            .fail(function (xhr) {
                const json = xhr.responseJSON || {};
                toastr.error(json.errors ? Object.values(json.errors)[0][0] : json.message || 'Could not save the subjects.');
                $button.prop('disabled', false);
            });
    });
})();
