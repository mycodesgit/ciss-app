/**
 * NSTP > CWTS / LTS / ROTC / NSTP 1/2 Students (resources/views/pages/employee/nstp/students/index.blade.php).
 * Server: NstpStudentsController. The CHED serial number list (22 columns): the table and wire:navigate handling
 * come from CissCrud (public/js/employee/crud.js); searching reloads the table, Excel exports every column.
 * Serial No. and Program Level are left empty for CHED / the NSTP office to fill in (as in the old system).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const page = () => document.getElementById('nstpStudents');
    const form = () => document.getElementById('nstpStudentsSearchForm');
    // HEI Name / Institutional Code / Type: the same on every row (config ciss.nstp_hei)
    const heiColumn = (key) => CissCrud.escapeHtml(JSON.parse(page().dataset.hei || '{}')[key]);

    // Filters of the last table load, so the export title matches the rows on screen
    let shownFilters = {};
    function loadFilters() {
        const elements = form().elements;
        shownFilters = { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value };
        return shownFilters;
    }

    function shownSearch() {
        if (!shownFilters.semester) return '';
        const semester = form().elements.semester.querySelector('option[value="' + shownFilters.semester + '"]');
        return (semester ? semester.textContent : '') + ' ' + shownFilters.schlyear + ' (' + shownFilters.campus + ')';
    }

    CissCrud.register({
        pageId: 'nstpStudents',
        table: '#nstpStudentsTable',
        order: [[6, 'asc'], [7, 'asc']],
        ajaxData: loadFilters,
        columns: [
            { data: null, orderable: false, searchable: false, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'studID', render: text },
            { data: 'schlyear', render: text },
            { data: 'program', render: text, defaultContent: '' },
            { data: 'region', render: text, defaultContent: '' },
            { data: null, orderable: false, searchable: false, render: () => '' },
            { data: 'lname', render: text, defaultContent: '' },
            { data: 'fname', render: text, defaultContent: '' },
            { data: 'ext', render: text, defaultContent: '' },
            { data: 'mname', render: text, defaultContent: '' },
            { data: 'bday', render: text, defaultContent: '' },
            { data: 'gender', render: text, defaultContent: '' },
            { data: 'brgy', render: text, defaultContent: '' },
            { data: 'city', render: text, defaultContent: '' },
            { data: 'province', render: text, defaultContent: '' },
            { data: null, orderable: false, searchable: false, render: () => heiColumn('name') },
            { data: null, orderable: false, searchable: false, render: () => heiColumn('institutional_code') },
            { data: null, orderable: false, searchable: false, render: () => heiColumn('type') },
            { data: null, orderable: false, searchable: false, render: () => '' },
            { data: 'progName', render: text, defaultContent: '' },
            { data: 'email', render: text, defaultContent: '' },
            { data: 'contact', render: text, defaultContent: '' },
        ],
        tableOptions: {
            language: { emptyTable: 'No students to show. Select a campus, school year and semester, then click Search.' },
            responsive: false,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'NSTP - ' + page().dataset.component + ' Students - ' + shownSearch(),
            }],
            drawCallback: () => $('#nstpStudents .js-search-shown').text(shownSearch()),
        },
    });

    $(document).on('submit', '#nstpStudentsSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
