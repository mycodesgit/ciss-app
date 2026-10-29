/**
 * Admission > Reports > Applicants (resources/views/pages/employee/admission/report/applicants.blade.php).
 * Shared admission actions come from admission/common.js (AdmissionUi); table and wire:navigate
 * handling from public/js/employee/crud.js (CissCrud).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();

    // Filters of the last table load, so exports and ZIP batches match the rows on screen
    let shownFilters = {};
    const loadFilters = () => (shownFilters = AdmissionUi.searchFilters());

    // Exports leave out the Action column; "#" is exported as shown on screen
    const exportOptions = {
        columns: ':not(:last-child)',
        format: { body: (data, row, column, node) => column === 0 ? node.textContent : data },
    };
    const title = () => 'Applicants Report - ' + shownFilters.campus + ' ' + shownFilters.year + ' - ' + (shownFilters.strand === 'all' ? 'All Strands' : shownFilters.strand);

    CissCrud.register({
        pageId: 'applicantsReport',
        table: '#appsreplistTable',
        order: [[1, 'asc']],
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
            { data: 'lstsch_attended', render: text },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([
                    { action: 'view', icon: 'fas fa-eye', label: 'View Data' },
                    { action: 'admissionSlip', icon: 'fas fa-file-pdf', label: 'Admission Slip', color: '#dc3545' },
                ]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No applicants to show. Select a year, campus and strand, then click Search.' },
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            dom: "<'row mb-2'<'col-md-4'l><'col-md-4 text-center'B><'col-md-4'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-md-5'i><'col-md-7'p>>",
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm', title, exportOptions },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm', title, exportOptions, orientation: 'landscape', pageSize: 'LEGAL' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn btn-secondary btn-sm', title, exportOptions },
            ],
            // Row numbers follow the current sort / search, across all pages
            drawCallback: function () {
                this.api().column(0, { search: 'applied', order: 'applied' }).nodes().each((cell, i) => { cell.textContent = i + 1; });
            },
        },
        actions: {
            view: AdmissionUi.actions.view,

            // Admission Slip PDF in the modal's iframe
            admissionSlip(row) {
                AdmissionUi.openPdf('#admissionSlipModal', row.display_name, CissCrud.urlFor('slipUrl', row.id));
            },
        },
    });

    // "Download Admission Slips": one ZIP link per batch of the applicants on screen
    $(document).on('click', '#applicantsReport .js-slip-batches', function () {
        const page = document.getElementById('applicantsReport');
        const perZip = parseInt(page.dataset.slipsPerZip, 10);
        const total = $('#appsreplistTable').DataTable().rows().count();

        if (!total) {
            toastr.warning('Search first: there are no applicants to download.');
            return;
        }

        const links = [];
        for (let offset = 0; offset < total; offset += perZip) {
            const url = page.dataset.slipsUrl + '?' + $.param(Object.assign({ offset: offset }, shownFilters));
            const label = (offset + 1) + ' - ' + Math.min(offset + perZip, total);

            links.push('<a href="' + CissCrud.escapeHtml(url) + '" class="btn btn-outline-success btn-sm js-slip-batch">' +
                '<i class="fas fa-download"></i> ' + label + '</a>');
        }

        const $modal = $('#slipBatchModal');
        $modal.find('.js-slip-batch-summary').text(total + ' applicant(s) - ' + title().replace('Applicants Report - ', ''));
        $modal.find('.js-slip-batch-buttons').html(links.join(''));
        $modal.modal('show');
    });

    // The browser downloads the ZIP when it is ready (the page stays as it is)
    $(document).on('click', '#applicantsReport .js-slip-batch', function () {
        this.classList.replace('btn-outline-success', 'btn-success');
        toastr.info('Preparing the ZIP file. The download starts when it is ready.');
    });
})();
