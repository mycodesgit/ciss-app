/**
 * Enrollment > View Student Grades (resources/views/pages/employee/enrollment/student-grades/index.blade.php).
 * Server: StudentGradesController. Search a Student ID -> the student's grades, one group per term
 * (school year + semester) with the term's credits. Listeners are on document, so they work after wire:navigate.
 */
(function () {
    'use strict';

    const escape = (text) => CissCrud.escapeHtml(text);
    const page = () => document.getElementById('studentGrades');

    function errorMessage(xhr, fallback) {
        const json = xhr.responseJSON || {};
        return json.errors ? Object.values(json.errors)[0][0] : (json.message || fallback);
    }

    const semesterBadge = (term) => CissCrud.badge(['', 'bg-primary', 'bg-success', 'bg-secondary'][term.semester] || 'bg-info', term.label);

    // A term's header row, then its subjects
    function termRows(term) {
        const header = '<tr class="table-light">' +
            '<td colspan="5" class="fw-semibold">' + semesterBadge(term) + '</td>' +
            '<td class="fw-semibold">' + term.credits + '</td>' +
            '</tr>';

        return header + term.rows.map((row) => '<tr>' +
            '<td>' + escape(row.course || '') + '</td>' +
            '<td>' + escape(row.subject || '') + '</td>' +
            '<td>' + escape(row.title || '') + '</td>' +
            '<td><b class="' + (row.gradeWarning ? 'text-danger' : '') + '">' + escape(row.grade || '') + '</b></td>' +
            '<td><b>' + escape(row.completion || '') + '</b></td>' +
            '<td>' + row.credit + '</td>' +
            '</tr>').join('');
    }

    function showGrades(response) {
        const $page = $(page());

        $page.find('.js-student-id-shown').text(response.student.stud_id);
        $page.find('.js-student-name').text(response.student.name);
        $page.find('.js-student-campus').text(response.student.campus);
        $page.find('.js-total-credits').text(response.credits);
        $page.find('.js-grade-rows').html(response.terms.length
            ? response.terms.map(termRows).join('')
            : '<tr><td colspan="6" class="text-center text-muted">No grades found for this student.</td></tr>');
        $page.find('.js-result').removeClass('d-none');
    }

    $(document).on('submit', '#studentGradesSearchForm', function (e) {
        e.preventDefault();
        const $submit = $(this).find('[type="submit"]').prop('disabled', true);

        $.get(page().dataset.findUrl, $(this).serialize())
            .done(showGrades)
            .fail(function (xhr) {
                $(page()).find('.js-result').addClass('d-none');
                toastr.error(errorMessage(xhr, 'Could not load the grades.'));
            })
            .always(() => $submit.prop('disabled', false));
    });
})();
