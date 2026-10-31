/**
 * Enrollment > Students Enrolled (resources/views/pages/employee/enrollment/students-enrolled/index.blade.php).
 * Server: StudentsEnrolledController. The table and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js); searching reloads the table, Excel exports every column.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const form = () => document.getElementById('studentsEnrolledSearchForm');

    // Filters of the last table load, so the export title matches the rows on screen
    let shownFilters = {};
    function loadFilters() {
        const elements = form().elements;
        shownFilters = { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value, type: elements.type.value };
        return shownFilters;
    }

    function shownSearch() {
        if (!shownFilters.semester) return '';
        const elements = form().elements;
        const label = (select, value) => (select.querySelector('option[value="' + value + '"]') || {}).textContent || '';
        return label(elements.type, shownFilters.type) + ' - ' + label(elements.semester, shownFilters.semester) + ' ' + shownFilters.schlyear + ' (' + shownFilters.campus + ')';
    }

    const column = (data) => ({ data, render: text, defaultContent: '' });

    CissCrud.register({
        pageId: 'studentsEnrolled',
        table: '#studentsEnrolledTable',
        order: [[1, 'asc']],
        ajaxData: loadFilters,
        columns: [
            column('studentID'),
            column('name'),
            column('strand'),
            // Shown as "Jan 05, 2003", sorted by the date
            { data: 'bday', render: (bday, type, row) => type === 'sort' ? (row.bday_sort || '') : CissCrud.escapeHtml(bday || ''), defaultContent: '' },
            { data: 'age', defaultContent: '' },
            column('gender'),
            column('type'),
            column('progName'),
            column('progAcronym'),
            column('studYear'),
            column('studSec'),
            column('term'),
            column('address'),
            column('brgy'),
            column('city'),
            column('province'),
            column('region'),
            column('zcode'),
            column('lastSchool'),
        ],
        tableOptions: {
            language: { emptyTable: 'No students to show. Select a campus, school year, semester and type, then click Search.' },
            responsive: false,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'Students Enrolled - ' + shownSearch(),
            }],
            drawCallback: function () {
                const total = this.api().data().count();
                $('#studentsEnrolled .js-search-shown').text(shownFilters.semester ? shownSearch() + ' · ' + total + ' student(s)' : '');
            },
        },
    });

    $(document).on('submit', '#studentsEnrolledSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
