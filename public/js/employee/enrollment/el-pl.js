/**
 * Enrollment > EL and PL (resources/views/pages/employee/enrollment/el-pl/index.blade.php).
 * Server: ElPlController. The table and wire:navigate handling come from CissCrud (public/js/employee/crud.js),
 * the Course select from course-select.js; searching reloads the table.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const form = () => document.getElementById('elPlSearchForm');

    // Filters of the last table load, so the export title matches the rows on screen
    let shownFilters = {};
    function loadFilters() {
        const elements = form().elements;
        shownFilters = { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value, progCod: elements.progCod.value };
        return shownFilters;
    }

    function shownSearch() {
        if (!shownFilters.progCod) return '';
        const elements = form().elements;
        const semester = elements.semester.querySelector('option[value="' + shownFilters.semester + '"]');
        const course = elements.progCod.querySelector('option[value="' + CSS.escape(shownFilters.progCod) + '"]');
        return (course ? course.textContent : shownFilters.progCod) + ' - ' + (semester ? semester.textContent : '') + ' ' + shownFilters.schlyear + ' (' + shownFilters.campus + ')';
    }

    CissCrud.register({
        pageId: 'elPl',
        table: '#elPlTable',
        order: [[8, 'asc'], [1, 'asc'], [2, 'asc']],
        ajaxData: loadFilters,
        columns: [
            { data: 'studentID', render: text },
            { data: 'lname', render: text },
            { data: 'fname', render: text },
            { data: 'mname', render: text, defaultContent: '' },
            { data: 'ext', render: text },
            { data: 'gender', render: text, defaultContent: '' },
            { data: 'address', render: text, defaultContent: '' },
            { data: 'degree', render: text, defaultContent: '' },
            { data: 'studYear', render: text },
            { data: 'subject', render: text },
            { data: 'grade', render: text, defaultContent: '' },
            { data: 'units', render: text, defaultContent: '' },
        ],
        tableOptions: {
            language: { emptyTable: 'No students to show. Select a campus, school year, semester and course, then click Search.' },
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'EL and PL - ' + shownSearch(),
            }],
            drawCallback: () => $('#elPl .js-search-shown').text(shownSearch()),
        },
    });

    $(document).on('submit', '#elPlSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
