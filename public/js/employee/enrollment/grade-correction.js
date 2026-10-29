/**
 * Enrollment > Correction of Grades (resources/views/pages/employee/enrollment/correction/). Server: GradeCorrectionController.
 *  - index: pick a term -> its subjects (CissCrud table, no page reload)
 *  - show:  every final grade and completion is a select, even submitted ones and INC. Picking a value asks for
 *           confirmation (old -> new), then saves; the server's answer replaces the row. "No" puts the old value back.
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
                    : '<tr><td colspan="8" class="text-center text-muted py-3">No students in this subject.</td></tr>');
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

    function rowHtml(row, number) {
        return '<tr data-id="' + row.id + '" data-number="' + number + '">' +
            '<td>' + number + '</td>' +
            '<td>' + escape(row.studID) + '</td>' +
            '<td><strong>' + escape(row.name) + '</strong></td>' +
            '<td>' + gradeSelect('grade', row.grade) + '</td>' +
            '<td>' + (row.completionShown ? gradeSelect('completion', row.completion) : '') + '</td>' +
            '<td><strong' + (row.equivalentWarning ? ' class="text-danger"' : '') + '>' + escape(row.equivalent || '') + '</strong></td>' +
            '<td><strong>' + escape(row.credit ?? '') + '</strong></td>' +
            '<td>' + (row.submitted ? CissCrud.badge('bg-success', 'Submitted') : (row.grade ? CissCrud.badge('bg-warning text-dark', 'Encoded') : '')) + '</td>' +
            '</tr>';
    }

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
                    replaceRow(row);
                    toastr.error(errorMessage(xhr, 'Could not correct the grade.'));
                });
        });
    });
})();
