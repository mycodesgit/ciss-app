/**
 * Enrollment > Class Attendance (resources/views/pages/employee/enrollment/class-attendance/index.blade.php).
 * Server: ClassAttendanceController. The table and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js); searching reloads the table without a page reload. View opens a subject's
 * students in a modal; its Attendance Sheet tab loads the subject's PDF.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const form = () => document.getElementById('classAttendanceSearchForm');

    // Filters of the last table load, so the export title matches the rows on screen
    let shownFilters = {};
    function loadFilters() {
        const elements = form().elements;
        shownFilters = { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value };
        return shownFilters;
    }

    function shownTerm() {
        const semester = form().querySelector('select[name="semester"] option[value="' + shownFilters.semester + '"]');
        return shownFilters.semester && semester ? semester.textContent + ' ' + shownFilters.schlyear + ' (' + shownFilters.campus + ')' : '';
    }

    const sum = (api, column) => api.column(column, { search: 'applied' }).data().toArray().reduce((total, value) => total + Number(value || 0), 0);

    CissCrud.register({
        pageId: 'classAttendance',
        table: '#classAttendanceTable',
        order: [[0, 'asc'], [2, 'asc']],
        ajaxData: loadFilters,
        columns: [
            { data: 'subject', render: text },
            { data: 'title', render: text, defaultContent: '' },
            { data: 'section', render: text, defaultContent: '' },
            { data: 'total', render: (total, type) => type === 'display' ? '<strong>' + total + '</strong>' : total },
            { data: 'male' },
            { data: 'female' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([{ action: 'view', icon: 'fas fa-eye', label: 'View Attendance' }]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No subjects to show. Select a campus, school year and semester, then click Search.' },
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'Class Attendance - ' + shownTerm(),
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
            }],
            // Totals of the rows shown by the search box
            footerCallback: function () {
                const api = this.api();
                const $footer = $(api.table().footer());
                $footer.find('.js-total').text(sum(api, 3));
                $footer.find('.js-male').text(sum(api, 4));
                $footer.find('.js-female').text(sum(api, 5));
            },
            drawCallback: () => $('#classAttendance .js-term-shown').text(shownTerm()),
        },
        actions: {
            view(row) {
                const $modal = $('#classAttendanceModal');

                $.get(row.students_url)
                    .done(function (response) {
                        $modal.find('.js-class-name').text(row.subject + ' (' + (row.section || '') + ')');
                        $modal.find('.js-student-rows').html(response.data.length
                            ? response.data.map((student, i) => '<tr>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(student.studentID) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(student.name) + '</td>' +
                                '<td>' + CissCrud.escapeHtml(student.gender || '') + '</td>' +
                                '</tr>').join('')
                            : '<tr><td colspan="4" class="text-center text-muted">No students enrolled in this subject.</td></tr>');

                        // The PDF loads only when its tab is opened
                        $modal.find('.js-class-pdf').removeAttr('src').attr('data-src', row.pdf_url);
                        $modal.find('.js-first-tab').tab('show');
                        $modal.modal('show');
                    })
                    .fail(() => toastr.error('Could not load the students.'));
            },
        },
    });

    $(document).on('shown.bs.tab', '#classAttendanceModal [data-bs-target="#classAttendancePdfTab"]', function () {
        const frame = document.querySelector('#classAttendanceModal .js-class-pdf');
        if (frame.dataset.src && frame.getAttribute('src') !== frame.dataset.src) frame.src = frame.dataset.src;
    });

    // Unload the PDF when the modal closes
    $(document).on('hidden.bs.modal', '#classAttendanceModal', function () {
        $(this).find('.js-class-pdf').removeAttr('src');
    });

    $(document).on('submit', '#classAttendanceSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
