/**
 * Admission > List of Examinee (resources/views/pages/employee/admission/examinee/list.blade.php).
 * Shared admission actions come from admission/common.js (AdmissionUi); table, modal-form saving
 * and wire:navigate handling from public/js/employee/crud.js (CissCrud).
 */
(function () {
    'use strict';

    const ACTIONS = CissCrud.actionsMenu([
        { action: 'view', icon: 'fas fa-eye', label: 'View Data' },
        { action: 'schoolId', icon: 'fas fa-image', label: 'School ID' },
        { action: 'reportCard', icon: 'fas fa-image', label: 'Report Card/TOR' },
        { action: 'proof', icon: 'fas fa-image', label: 'Proof/Evidence' },
        { action: 'schedule', icon: 'fas fa-calendar', label: 'Schedule' },
        { action: 'result', icon: 'fas fa-file-lines', label: 'Assign Result' },
        { action: 'push', icon: 'fas fa-check', label: 'Push to Result', color: 'green' },
        { action: 'cancel', icon: 'fas fa-trash', label: 'Delete', color: 'red' },
    ]);

    const text = $.fn.dataTable.render.text();

    // Stanine + remark preview for a raw score (the server computes the saved values the same way)
    function previewResult(input) {
        const stanines = JSON.parse(input.dataset.stanines);
        const remarks = JSON.parse(input.dataset.remarks);
        const score = parseInt(input.value, 10);

        let stanine = null;
        if (score >= 1) {
            const bound = Object.keys(stanines).map(Number).sort((a, b) => a - b).find((max) => score <= max);
            stanine = bound ? stanines[bound] : null;
        }

        const $modal = $(input).closest('.modal');
        $modal.find('.js-stanine').val(stanine || '');
        $modal.find('.js-remark').val(stanine ? remarks[stanine] : '');
    }

    $(document).on('input', '.js-raw-score', function () {
        previewResult(this);
    });

    CissCrud.register({
        pageId: 'examinees',
        table: '#examlistTable',
        order: [[0, 'asc']],
        ajaxData: AdmissionUi.searchFilters,
        columns: [
            { data: 'admission_id', render: text },
            { data: 'display_name', render: text },
            { data: 'type_name', render: text },
            { data: 'contact', render: text },
            { data: 'schedule_at', render: (at, type, row) => type === 'display' ? CissCrud.escapeHtml(row.schedule_label || '') : at },
            { data: 'campus', render: text },
            { data: 'strand', render: text },
            {
                data: 'raw_score',
                render: (score, type) => {
                    const encoded = score !== null && score !== '';
                    if (type !== 'display') return encoded ? 'Encoded' : 'Not Encoded';
                    return encoded
                        ? '<span class="badge bg-success"><i class="ti ti-check"></i> Encoded</span>'
                        : '<span class="badge bg-warning"><i class="ti ti-cancel"></i> Not Encoded</span>';
                },
            },
            { data: null, orderable: false, searchable: false, render: (data, type, row) => row.can_manage ? ACTIONS : '' },
        ],
        actions: Object.assign({}, AdmissionUi.actions, {
            result(examinee) {
                const { $modal, form } = CissCrud.openFor('#resultModal', examinee);
                AdmissionUi.showFields($modal, examinee);
                form.elements.raw_score.value = examinee.raw_score ?? '';
                previewResult(form.elements.raw_score);
                $modal.modal('show');
            },

            push(examinee) {
                AdmissionUi.confirmThen({
                    title: 'Push to Examination Result List?',
                    html: CissCrud.escapeHtml(examinee.display_name) + ' will be moved to the examination result list.',
                    confirmButtonText: 'Yes, push',
                }, () => CissCrud.send('pushUrl', examinee.id));
            },
        }),
    });
})();
