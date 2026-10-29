/**
 * Enrollment > Gradesheet (resources/views/pages/employee/enrollment/gradesheet/). Server: GradesheetController.
 *  - index: pick a term -> its subjects (CissCrud table, no page reload)
 *  - show:  a subject's students. A grade / completion saves as soon as it is picked; the server answers with the
 *           row as it is now (locked or not, credits, equivalent), which replaces the row on screen.
 *           Submit Grades locks the encoded grades; Edit Grades / Completion unlocks one with the grades password.
 * The server decides what may be changed; the page only shows it. Pushed to <head>, so with wire:navigate it
 * loads once (delegated handlers bound once; the gradesheet is loaded on every livewire:navigated that lands on it).
 */
(function () {
    'use strict';

    const escape = (text) => CissCrud.escapeHtml(text);

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    function put(url, data) {
        return $.ajax({
            url,
            type: 'POST',
            data: Object.assign({ _method: 'PUT' }, data),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        });
    }

    // ---------- Subjects of a term (index) ----------

    CissCrud.register({
        pageId: 'gradesheetSubjects',
        table: '#gradesheetSubjectsTable',
        order: [[1, 'asc']],
        ajaxData: function () {
            const form = document.getElementById('gradesheetSearchForm');
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
                    ? '<a href="' + escape(url) + '" wire:navigate class="btn btn-success btn-sm text-light" title="Open gradesheet"><i class="fas fa-eye"></i></a>'
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

    $(document).on('submit', '#gradesheetSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    // ---------- Gradesheet of a subject (show) ----------

    let rows = new Map(); // studgrades id => row from the server

    const page = () => document.getElementById('gradesheet');
    const gradeCodes = () => JSON.parse(page().dataset.gradeCodes || '[]');

    document.addEventListener('livewire:navigated', function () {
        if (page()) loadStudents();
    });

    function loadStudents() {
        $.get(page().dataset.studentsUrl)
            .done(function (response) {
                rows = new Map(response.data.map((row) => [row.id, row]));
                renderTable();
                setPending(response.pending);
            })
            .fail((xhr) => toastr.error(errorMessage(xhr, 'Could not load the students.')));
    }

    function renderTable() {
        const list = Array.from(rows.values());

        $('#gradesheetTable tbody').html(list.length
            ? list.map((row, index) => rowHtml(row, index + 1)).join('')
            : '<tr><td colspan="8" class="text-center text-muted py-3">No students in this subject.</td></tr>');
    }

    // Replaces one row with the server's version (keeps its number)
    function replaceRow(row) {
        rows.set(row.id, row);
        const $tr = $('#gradesheetTable tr[data-id="' + row.id + '"]');
        $tr.replaceWith(rowHtml(row, $tr.data('number')));
    }

    function setPending(pending) {
        $('#submitGradesButton').prop('disabled', !pending);
        $('#gradesheet .js-pending-note').text(pending ? pending + ' encoded grade(s) not yet submitted' : '');
    }

    // A grade select; the current grade is kept as an option even if it isn't one the user may pick
    function gradeSelect(kind, current) {
        const codes = gradeCodes();
        if (current && !codes.includes(current)) codes.unshift(current);

        return '<select class="form-select form-select-sm js-' + kind + '">' +
            '<option value=""></option>' +
            codes.map((code) => '<option value="' + escape(code) + '"' + (code === current ? ' selected' : '') + '>' + escape(code) + '</option>').join('') +
            '</select>';
    }

    const locked = (value, warning) => '<strong' + (warning ? ' class="text-danger"' : '') + '>' + escape(value || '') + '</strong>';

    function rowHtml(row, number) {
        const actions = [];
        if (row.canUnlockGrade) actions.push({ action: 'grade', icon: 'fas fa-pen', label: 'Edit Grades' });
        if (row.canUnlockCompletion) actions.push({ action: 'completion', icon: 'fa-solid fa-envelopes-bulk', label: 'Completion' });

        return '<tr data-id="' + row.id + '" data-number="' + number + '">' +
            '<td>' + number + '</td>' +
            '<td>' + escape(row.studID) + '</td>' +
            '<td><strong>' + escape(row.name) + '</strong></td>' +
            '<td>' + (row.gradeEditable ? gradeSelect('grade', row.grade) : locked(row.grade, row.gradeWarning)) + '</td>' +
            '<td>' + (!row.completionShown ? '' : (row.completionEditable ? gradeSelect('completion', row.completion) : locked(row.completion))) + '</td>' +
            '<td>' + locked(row.equivalent, row.equivalentWarning) + '</td>' +
            '<td><strong>' + escape(row.credit ?? '') + '</strong></td>' +
            '<td class="text-center">' + (actions.length
                ? '<div class="dropdown"><button type="button" class="btn btn-success btn-sm text-light dropdown-toggle" data-bs-toggle="dropdown"></button>' +
                  '<div class="dropdown-menu">' + actions.map((item) => '<a href="#" class="dropdown-item js-unlock" data-kind="' + item.action + '"><i class="' + item.icon + '"></i> ' + item.label + '</a>').join('') + '</div></div>'
                : '') + '</td>' +
            '</tr>';
    }

    // Picking a grade / completion saves it; on an error the row goes back to what the server has
    $(document).on('change', '#gradesheetTable .js-grade, #gradesheetTable .js-completion', function () {
        const select = this;
        const row = rows.get(Number(select.closest('tr').dataset.id));
        const url = select.classList.contains('js-grade') ? row.saveGradeUrl : row.saveCompletionUrl;

        select.disabled = true;

        put(url, { grade: select.value })
            .done(function (response) {
                replaceRow(response.row);
                setPending(response.pending);
                toastr.success(response.message);
            })
            .fail(function (xhr) {
                replaceRow(row);
                toastr.error(errorMessage(xhr, 'Could not save the grade.'));
            });
    });

    // Edit Grades / Completion of a submitted grade: ask for the grades password
    $(document).on('click', '#gradesheetTable .js-unlock', function (e) {
        e.preventDefault();

        const row = rows.get(Number(this.closest('tr').dataset.id));
        const isGrade = this.dataset.kind === 'grade';
        const form = document.getElementById('unlockGradeForm');

        form.reset();
        form.dataset.url = isGrade ? row.unlockGradeUrl : row.unlockCompletionUrl;
        $(form.elements.password).removeClass('is-invalid').next('.invalid-feedback').remove();
        $('#unlockGradeModal .js-unlock-question').text(
            'Are you sure you want to edit the ' + (isGrade ? 'grade' : 'completion grade') + ' of ' + row.name + '?'
        );
        $('#unlockGradeModal').modal('show');
    });

    $(document).on('shown.bs.modal', '#unlockGradeModal', function () {
        document.getElementById('unlockGradeForm').elements.password.focus();
    });

    $(document).on('submit', '#unlockGradeForm', function (e) {
        e.preventDefault();

        const form = this;
        const $password = $(form.elements.password);
        const $submit = $(form).find('[type="submit"]').prop('disabled', true);

        put(form.dataset.url, { password: form.elements.password.value })
            .done(function (response) {
                $('#unlockGradeModal').modal('hide');
                replaceRow(response.row);
                setPending(response.pending);
                toastr.success(response.message);
            })
            .fail(function (xhr) {
                const message = xhr.status === 429 ? 'Too many tries. Wait a minute, then try again.' : errorMessage(xhr, 'Could not unlock the grade.');
                $password.addClass('is-invalid').next('.invalid-feedback').remove();
                $password.after('<div class="invalid-feedback">' + escape(message) + '</div>');
            })
            .always(() => $submit.prop('disabled', false));
    });

    // Submit Grades: lock every encoded grade / completion of the subject
    $(document).on('click', '#gradesheet #submitGradesButton', function () {
        Swal.fire({
            title: 'Submit the grades?',
            text: 'Submitted grades are locked. Changing one later needs the grades password.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Submit',
        }).then(function (result) {
            if (!result.isConfirmed) return;

            put(page().dataset.submitUrl)
                .done(function (response) {
                    toastr.success(response.message);
                    loadStudents();
                })
                .fail((xhr) => Swal.fire({ icon: 'error', title: 'Not submitted', text: errorMessage(xhr, 'Failed to submit grades.') }));
        });
    });
})();
