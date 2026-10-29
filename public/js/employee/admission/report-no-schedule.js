/**
 * Admission > Reports > Applicants No Sched (resources/views/pages/employee/admission/report/no-schedule.blade.php).
 * Search form from admission/common.js (AdmissionUi); table and wire:navigate handling from
 * public/js/employee/crud.js (CissCrud).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();

    // Filters of the last table load, so the exports match the rows on screen
    let shownFilters = {};
    const loadFilters = () => (shownFilters = AdmissionUi.searchFilters());
    const strandName = () => shownFilters.strand === 'all' ? 'All Strands' : shownFilters.strand;

    // Printable list (header / footer, Legal) of the search on screen, in the PDF modal's iframe
    function openPdf() {
        if (!shownFilters.strand) {
            toastr.warning('Search first: select a strand and click Search.');
            return;
        }

        const url = document.getElementById('noScheduleReport').dataset.pdfUrl + '?' + $.param(shownFilters);
        AdmissionUi.openPdf('#noSchedulePdfModal', shownFilters.campus + ' ' + shownFilters.year + ' - ' + strandName(), url);
    }

    CissCrud.register({
        pageId: 'noScheduleReport',
        table: '#appsnoschedlistTable',
        order: [[2, 'asc']],
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
            { data: 'registered_at', render: (date, type, row) => type === 'display' ? CissCrud.escapeHtml(row.registered_label) : date },
            { data: 'missing', render: (missing, type) => type === 'display' ? CissCrud.badge('bg-warning text-dark', missing) : missing },
        ],
        tableOptions: {
            language: { emptyTable: 'No applicants to show. Select a year, campus and strand, then click Search.' },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: () => 'Applicants No Schedule - ' + shownFilters.campus + ' ' + shownFilters.year + ' - ' + strandName(),
                    // "#" is exported as shown on screen
                    exportOptions: { format: { body: (data, row, column, node) => column === 0 ? node.textContent : data } },
                },
                {
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    action: openPdf,
                },
            ],
            // Row numbers follow the current sort / search, across all pages
            drawCallback: function () {
                this.api().column(0, { search: 'applied', order: 'applied' }).nodes().each((cell, i) => { cell.textContent = i + 1; });
            },
        },
        actions: {},
    });
})();
