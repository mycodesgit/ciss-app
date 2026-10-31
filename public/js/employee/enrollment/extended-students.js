/**
 * Enrollment > Extended Students (resources/views/pages/employee/enrollment/extended-students/index.blade.php).
 * Server: ExtendedStudentsController. The table and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js); View loads the student's enrollments from Enrollment History.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();

    // First M. Last Ext (as in coasv2.0)
    function fullName(row) {
        const middle = row.mname ? row.mname.substr(0, 1) + '. ' : '';
        const ext = row.ext && row.ext !== 'N/A' ? ' ' + row.ext : '';
        return row.fname + ' ' + middle + row.lname + ext;
    }

    CissCrud.register({
        pageId: 'extendedStudents',
        table: '#extendedStudTable',
        order: [[2, 'desc']],
        columns: [
            { data: 'stud_id', render: text },
            { data: null, render: (row) => CissCrud.escapeHtml(fullName(row)) },
            { data: 'semesters' },
            { data: 'campus', render: text },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([{ action: 'history', icon: 'fas fa-eye', label: 'Enrollment History' }]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No extended students.' },
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Extended Students',
                exportOptions: { columns: [0, 1, 2, 3] },
            }],
        },
        actions: {
            // The student's enrollments (all terms) in a modal
            history(row) {
                const $modal = $('#extendedStudHisModal');

                $.get(row.history_url)
                    .done(function (response) {
                        $modal.find('.js-student-name').text(response.name);
                        $modal.find('.js-history-rows').html(response.data.length
                            ? response.data.map((history) => '<tr>' +
                                '<td>' + CissCrud.escapeHtml(history.studentID) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.schlyear) + '</td>' +
                                '<td>' + CissCrud.badge(history.semester === 3 ? 'bg-secondary' : 'bg-info', history.semester_name) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.course || '') + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.studYear) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.studSec) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(history.campus) + '</td>' +
                                '</tr>').join('')
                            : '<tr><td colspan="7" class="text-center text-muted">No enrollment history found.</td></tr>');
                        $modal.modal('show');
                    })
                    .fail(() => toastr.error('Could not load the enrollment history.'));
            },
        },
    });
})();
