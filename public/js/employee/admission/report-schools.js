/**
 * Admission > Reports > Applicants per School (resources/views/pages/employee/admission/report/schools.blade.php).
 * Search form from admission/common.js (AdmissionUi); table and wire:navigate handling from
 * public/js/employee/crud.js (CissCrud).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();

    // Filters of the last table load, for the Excel title
    let shownFilters = {};
    const loadFilters = () => (shownFilters = AdmissionUi.searchFilters());

    CissCrud.register({
        pageId: 'schoolsReport',
        table: '#appschoolreplistTable',
        order: [[8, 'asc'], [2, 'asc']], // by school, then name
        ajaxData: loadFilters,
        columns: [
            { data: null, orderable: false, searchable: false, defaultContent: '' }, // numbered in drawCallback
            { data: 'admission_id', render: text },
            { data: 'display_name', render: text },
            { data: 'type_name', render: text },
            { data: 'strand', render: text },
            { data: 'email', render: text },
            { data: 'contact', render: text },
            { data: 'campus_name', render: text },
            { data: 'last_school', render: text }, // lstsch_attended, or suc_lst_attended for transferees
        ],
        tableOptions: {
            language: { emptyTable: 'No applicants to show. Select a year, campus and strand, then click Search.' },
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'Applicants per School - ' + shownFilters.campus + ' ' + shownFilters.year + ' - ' + (shownFilters.strand === 'all' ? 'All Strands' : shownFilters.strand),
                // "#" is exported as shown on screen
                exportOptions: { format: { body: (data, row, column, node) => column === 0 ? node.textContent : data } },
            }],
            // Row numbers follow the current sort / search, across all pages
            drawCallback: function () {
                this.api().column(0, { search: 'applied', order: 'applied' }).nodes().each((cell, i) => { cell.textContent = i + 1; });
            },
        },
        actions: {},
    });
})();
