/**
 * Admission > Reports > Confirmed Applicants (resources/views/pages/employee/admission/report/confirmed.blade.php).
 * Search form and PDF modal from admission/common.js (AdmissionUi); table and wire:navigate handling from
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

        const url = document.getElementById('confirmedReport').dataset.pdfUrl + '?' + $.param(shownFilters);
        AdmissionUi.openPdf('#confirmedPdfModal', shownFilters.campus + ' ' + shownFilters.year + ' - ' + strandName(), url);
    }

    CissCrud.register({
        pageId: 'confirmedReport',
        table: '#appsconfirmedlistTable',
        order: [[2, 'asc']],
        ajaxData: loadFilters,
        columns: [
            { data: null, orderable: false, searchable: false, defaultContent: '' }, // numbered in drawCallback
            { data: 'admission_id', render: text },
            { data: 'display_name', render: text },
            { data: 'type_name', render: text },
            { data: 'contact', render: text },
            { data: 'remark', render: (remark, type, row) => type === 'display' ? CissCrud.badge(remark === 'Failed' ? 'bg-danger' : 'bg-success', remark) : remark },
            { data: 'strand', render: text },
            { data: 'campus_name', render: text },
        ],
        tableOptions: {
            language: { emptyTable: 'No confirmed applicants to show. Select a year, campus and strand, then click Search.' },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: () => 'Confirmed Applicants - ' + shownFilters.campus + ' ' + shownFilters.year + ' - ' + strandName(),
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
