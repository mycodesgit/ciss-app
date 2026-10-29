/**
 * Admission > Reports > Admission Schedules (resources/views/pages/employee/admission/report/schedules.blade.php).
 * PDF modal from admission/common.js (AdmissionUi); table and wire:navigate handling from
 * public/js/employee/crud.js (CissCrud).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();

    const form = () => document.getElementById('scheduleSearchForm');
    const page = () => document.getElementById('schedulesReport');

    // Filters of the last table load, so the exports match the rows on screen
    let shownFilters = {};
    function loadFilters() {
        const elements = form().elements;
        shownFilters = { year: elements.year.value, campus: elements.campus.value, schedule: elements.schedule.value };
        return shownFilters;
    }

    function shownScheduleLabel() {
        const option = form().querySelector('select[name="schedule"] option[value="' + shownFilters.schedule + '"]');
        return option ? option.textContent.replace(/\s\(\d+\/\d+\)$/, '') : '';
    }

    const title = () => 'Admission Schedule - ' + shownFilters.campus + ' ' + shownScheduleLabel();

    CissCrud.register({
        pageId: 'schedulesReport',
        table: '#appsschedlistTable',
        order: [[2, 'asc']],
        ajaxData: loadFilters,
        columns: [
            { data: null, orderable: false, searchable: false, defaultContent: '' }, // numbered in drawCallback
            { data: 'admission_id', render: text },
            { data: 'display_name', render: text },
            { data: 'type_name', render: text },
            { data: 'contact', render: text },
            { data: 'schedule_label', render: text },
            { data: 'venue', render: text },
            { data: 'campus_name', render: text },
        ],
        tableOptions: {
            language: { emptyTable: 'No applicants to show. Select a year, campus and admission date & time, then click Search.' },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title,
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
                $('#schedulesReport .js-schedule-shown').text(shownFilters.schedule ? shownScheduleLabel() : '');
            },
        },
        actions: {},
    });

    // Printable list (header / footer, Legal) of the schedule on screen, in the PDF modal's iframe
    function openPdf() {
        if (!shownFilters.schedule) {
            toastr.warning('Search a schedule first.');
            return;
        }

        AdmissionUi.openPdf('#schedulePdfModal', shownScheduleLabel(), page().dataset.pdfUrl + '?' + $.param(shownFilters));
    }

    // Admission Date & Time options for the selected year / campus
    function loadSchedules() {
        const select = form().elements.schedule;
        select.innerHTML = '<option value="">Loading schedules...</option>';

        $.get(page().dataset.schedulesUrl, { year: form().elements.year.value, campus: form().elements.campus.value })
            .done(function (schedules) {
                select.innerHTML = '<option value="">' + (schedules.length ? 'Select date & time' : 'No schedules for this year / campus') + '</option>' +
                    schedules.map((schedule) => '<option value="' + schedule.id + '">' + CissCrud.escapeHtml(schedule.label) + '</option>').join('');
            })
            .fail(() => toastr.error('Could not load the admission schedules.'));
    }

    document.addEventListener('livewire:navigated', function () {
        if (page()) loadSchedules();
    });

    $(document).on('change', '#scheduleSearchForm select[name="year"], #scheduleSearchForm select[name="campus"]', loadSchedules);

    $(document).on('submit', '#scheduleSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
