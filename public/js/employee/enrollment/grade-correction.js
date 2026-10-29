/**
 * Enrollment > Correction of Grades (resources/views/pages/employee/enrollment/correction/). Server: GradeCorrectionController.
 *  - index: pick a term -> its subjects (CissCrud table, no page reload)
 *  - show:  every row is locked. Unlock asks for the grades password (the server unlocks the row for a few minutes);
 *           then its final grade and completion are selects, even submitted ones and INC. Picking a value asks for
 *           confirmation (old -> new), then saves; the server's answer (locked again) replaces the row. "No" puts the
 *           old value back. The server refuses corrections of rows that aren't unlocked.
 * Pushed to <head>, so with wire:navigate it loads once (delegated handlers bound once).
 */
(function () {
    'use strict';

    const escape = (text) => CissCrud.escapeHtml(text);

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    // ---------- Subjects of a term (index) ----------

    CissCrud.register({
        pageId: 'gradeCorrectionSubjects',
        table: '#correctionSubjectsTable',
        order: [[1, 'asc']],
        ajaxData: function () {
            const form = document.getElementById('correctionSearchForm');
            return { schlyear: form.elements.schlyear.value, semester: form.elements.semester.value };
        },
        columns: [
            { data: null, orderable: false, searchable: false, defaultContent: '' }, // numbered in drawCallback
            { data: 'sub_name', render: $.fn.dataTable.render.text() },
            { data: 'sub_title', render: $.fn.dataTable.render.text() },
            { data: 'subSec', render: $.fn.dataTable.render.text() },
            {
                data: 'url',
                orderable: false,
                searchable: false,
                render: (url, type) => type === 'display'
                    ? '<a href="' + escape(url) + '" wire:navigate class="btn btn-success btn-sm text-light" title="Correct grades"><i class="fas fa-eye"></i></a>'
                    : '',
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No subjects to show. Select a school year and semester, then click OK.' },
            drawCallback: function () {
                this.api().column(0, { search: 'applied', order: 'applied' }).nodes().each((cell, i) => { cell.textContent = i + 1; });
            },
        },
        actions: {},
    });

    $(document).on('submit', '#correctionSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    // ---------- Grades of a subject (show) ----------

    let rows = new Map(); // studgrades id => row from the server

    const page = () => document.getElementById('gradeCorrection');
    const gradeCodes = () => JSON.parse(page().dataset.gradeCodes || '[]');
    const needsCompletion = (grade) => ['FAILURE', 'INC', 'Inc.', 'inc'].includes(grade);

    document.addEventListener('livewire:navigated', function () {
        if (page()) loadStudents();
    });

    function loadStudents() {
        $.get(page().dataset.studentsUrl)
            .done(function (response) {
                rows = new Map(response.data.map((row) => [row.id, row]));
                const list = Array.from(rows.values());
                $('#correctionTable tbody').html(list.length
                    ? list.map((row, index) => rowHtml(row, index + 1)).join('')
                    : '<tr><td colspan="9" class="text-center text-muted py-3">No students in this subject.</td></tr>');
            })
            .fail((xhr) => toastr.error(errorMessage(xhr, 'Could not load the students.')));
    }

    function replaceRow(row) {
        rows.set(row.id, row);
        const $tr = $('#correctionTable tr[data-id="' + row.id + '"]');
        $tr.replaceWith(rowHtml(row, $tr.data('number')));
    }

    // The current grade is kept as an option even if it isn't one the user may pick
    function gradeSelect(kind, current) {
        const codes = gradeCodes();
        if (current && !codes.includes(current)) codes.unshift(current);

        return '<select class="form-select form-select-sm js-correct-' + kind + '">' +
            '<option value=""></option>' +
            codes.map((code) => '<option value="' + escape(code) + '"' + (code === current ? ' selected' : '') + '>' + escape(code) + '</option>').join('') +
            '</select>';
    }

    const plain = (value, warning) => '<strong' + (warning ? ' class="text-danger"' : '') + '>' + escape(value || '') + '</strong>';

    // Locked: plain values + Unlock. Unlocked: selects (the server locks the row again after a correction)
    function rowHtml(row, number) {
        const grade = row.unlocked ? gradeSelect('grade', row.grade) : plain(row.grade, row.gradeWarning);
        const completion = !row.completionShown ? '' : (row.unlocked ? gradeSelect('completion', row.completion) : plain(row.completion));

        return '<tr data-id="' + row.id + '" data-number="' + number + '"' + (row.unlocked ? ' class="table-warning"' : '') + '>' +
            '<td>' + number + '</td>' +
            '<td>' + escape(row.studID) + '</td>' +
            '<td><strong>' + escape(row.name) + '</strong></td>' +
            '<td>' + grade + '</td>' +
            '<td>' + completion + '</td>' +
            '<td>' + plain(row.equivalent, row.equivalentWarning) + '</td>' +
            '<td><strong>' + escape(row.credit ?? '') + '</strong></td>' +
            '<td>' + (row.submitted ? CissCrud.badge('bg-success', 'Submitted') : (row.grade ? CissCrud.badge('bg-warning text-dark', 'Encoded') : '')) + '</td>' +
            '<td class="text-center">' + (row.unlocked
                ? CissCrud.badge('bg-warning text-dark', 'Unlocked')
                : '<button type="button" class="btn btn-outline-success btn-sm js-unlock-row" title="Unlock with the grades password"><i class="ti ti-lock"></i> Unlock</button>') +
            '</td>' +
            '</tr>';
    }

    // Unlock: ask for the grades password
    $(document).on('click', '#correctionTable .js-unlock-row', function () {
        const row = rows.get(Number(this.closest('tr').dataset.id));
        const form = document.getElementById('unlockCorrectionForm');

        form.reset();
        form.dataset.url = row.unlockUrl;
        $(form.elements.password).removeClass('is-invalid').next('.invalid-feedback').remove();
        $('#unlockCorrectionModal .js-unlock-question').text('Unlock the grades of ' + row.name + ' for correction?');
        $('#unlockCorrectionModal').modal('show');
    });

    $(document).on('shown.bs.modal', '#unlockCorrectionModal', function () {
        document.getElementById('unlockCorrectionForm').elements.password.focus();
    });

    $(document).on('submit', '#unlockCorrectionForm', function (e) {
        e.preventDefault();

        const form = this;
        const $password = $(form.elements.password);
        const $submit = $(form).find('[type="submit"]').prop('disabled', true);

        $.ajax({
            url: form.dataset.url,
            type: 'POST',
            data: { _method: 'PUT', password: form.elements.password.value },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
            .done(function (response) {
                $('#unlockCorrectionModal').modal('hide');
                replaceRow(response.row);
                toastr.success(response.message);
            })
            .fail(function (xhr) {
                const message = xhr.status === 429 ? 'Too many tries. Wait a minute, then try again.' : errorMessage(xhr, 'Could not unlock the grade.');
                $password.addClass('is-invalid').next('.invalid-feedback').remove();
                $password.after('<div class="invalid-feedback">' + escape(message) + '</div>');
            })
            .always(() => $submit.prop('disabled', false));
    });

    // Pick -> confirm (old -> new) -> save; "No" or an error puts the row back as the server has it
    $(document).on('change', '#correctionTable .js-correct-grade, #correctionTable .js-correct-completion', function () {
        const select = this;
        const row = rows.get(Number(select.closest('tr').dataset.id));
        const isGrade = select.classList.contains('js-correct-grade');
        const oldValue = (isGrade ? row.grade : row.completion) || '(none)';
        const newValue = select.value || '(none)';

        // Correcting an INC / FAILURE to a regular grade drops its completion
        const dropsCompletion = isGrade && row.completion && !needsCompletion(select.value);

        Swal.fire({
            title: 'Correct the ' + (isGrade ? 'final grade' : 'completion') + '?',
            html: escape(row.name) + '<br><b>' + escape(oldValue) + ' &rarr; ' + escape(newValue) + '</b>' +
                (dropsCompletion ? '<br><span class="text-danger small">The completion ' + escape(row.completion) + ' will be removed.</span>' : ''),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Yes, correct it',
            cancelButtonText: 'No',
        }).then(function (result) {
            if (!result.isConfirmed) {
                replaceRow(row);
                return;
            }

            select.disabled = true;

            $.ajax({
                url: isGrade ? row.correctGradeUrl : row.correctCompletionUrl,
                type: 'POST',
                data: { _method: 'PUT', grade: select.value },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            })
                .done(function (response) {
                    replaceRow(response.row);
                    toastr.success(response.message);
                })
                .fail(function (xhr) {
                    // Reload from the server: e.g. the unlock expired, so the row shows locked again
                    loadStudents();
                    toastr.error(errorMessage(xhr, 'Could not correct the grade.'));
                });
        });
    });
})();
