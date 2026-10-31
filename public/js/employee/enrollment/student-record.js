/**
 * Enrollment > Student Record (resources/views/pages/employee/enrollment/student-record/index.blade.php).
 * Server: StudentRecordController. Search a Student ID -> the record as a PDF (iframe) and the Computation table:
 * one row per subject and a Term Total row (credits, average) after each term, exported to Excel by DataTables.
 * Listeners are on document, so they work after wire:navigate; the table is destroyed when leaving.
 */
(function () {
    'use strict';

    const escape = (text) => CissCrud.escapeHtml(text);
    const page = () => document.getElementById('studentRecord');
    const SEMESTERS = { 1: '1st Sem', 2: '2nd Sem', 3: 'Summer' };

    let table = null;
    let shownStudent = null;

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    function destroyTable() {
        if (table) table.destroy();
        table = null;
    }

    document.addEventListener('livewire:navigating', destroyTable);

    const cells = (values, className) => '<tr' + (className ? ' class="' + className + '"' : '') + '>' +
        values.map((value) => '<td>' + value + '</td>').join('') + '</tr>';

    // A term's subjects, then its Term Total row (11 cells each, so DataTables can export it)
    function termRows(term, studentId) {
        const rows = term.rows.map((row) => cells([
            escape(studentId),
            escape(term.schlyear),
            escape(SEMESTERS[term.semester] || term.semester),
            escape(row.course || ''),
            escape(row.subject || ''),
            escape(row.title || ''),
            '<b class="' + (row.gradeWarning ? 'text-danger' : '') + '">' + escape(row.grade || '') + '</b>',
            escape(row.rawGrade || ''),
            '<b>' + escape(row.completion || '') + '</b>',
            row.nstp ? '(' + row.credit + ')' : String(row.credit),
            row.product === null ? '' : row.product.toFixed(2),
        ]));

        const total = cells(['', '', '', '', '', '<b>Term Total: ' + escape(term.label) + '</b>', '', '', '',
            '<b>' + term.countedCredits + '</b>', '<b>' + (term.average === null ? 'N/A' : term.average.toFixed(2)) + '</b>'], 'table-warning');

        return rows.join('') + total;
    }

    function showRecord(response) {
        const $page = $(page());
        shownStudent = response.student;

        $page.find('.js-student-id-shown').text(response.student.stud_id);
        $page.find('.js-student-name').text(response.student.name);
        $page.find('.js-student-program').text(response.student.program || 'No program');
        $page.find('.js-student-campus').text(response.student.campus);
        $page.find('.js-record-pdf').attr('src', response.pdf_url);

        destroyTable();
        $page.find('#studentRecordTable tbody').html(response.terms.length
            ? response.terms.map((term) => termRows(term, response.student.stud_id)).join('')
            : '');

        // Rows stay in term order; DataTables only adds the Excel button
        table = $('#studentRecordTable').DataTable({
            ordering: false,
            paging: false,
            searching: false,
            info: false,
            language: { emptyTable: 'No grades found for this student.' },
            dom: 'Bt',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm mb-2',
                title: () => 'Student Record - ' + shownStudent.stud_id + ' ' + shownStudent.name,
            }],
        });

        $page.find('.js-result').removeClass('d-none');
    }

    $(document).on('submit', '#studentRecordSearchForm', function (e) {
        e.preventDefault();
        const $submit = $(this).find('[type="submit"]').prop('disabled', true);

        $.get(page().dataset.findUrl, $(this).serialize())
            .done(showRecord)
            .fail(function (xhr) {
                $(page()).find('.js-result').addClass('d-none');
                toastr.error(errorMessage(xhr, 'Could not load the student record.'));
            })
            .always(() => $submit.prop('disabled', false));
    });
})();
