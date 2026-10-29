/**
 * Admission > List of Applicant (resources/views/pages/employee/admission/applicant/list.blade.php).
 * Shared admission actions come from admission/common.js (AdmissionUi); table, modal-form saving
 * and wire:navigate handling from public/js/employee/crud.js (CissCrud).
 *
 * Flow: the table loads with the search form's values (year / campus / strand);
 * pressing Search reloads it. Each row has an action menu (only for your own campus).
 */
(function () {
    'use strict';

    // Re-upload status (ad_applicant_admission.status) => badge colour
    const REUPLOAD_BADGES = { 1: 'bg-success', 2: 'bg-warning', 3: 'bg-secondary' };

    const ACTIONS = CissCrud.actionsMenu([
        { action: 'view', icon: 'fas fa-eye', label: 'View Data' },
        { action: 'reupload', icon: 'fas fa-upload', label: 'Re-Upload' },
        { action: 'schoolId', icon: 'fas fa-id-card-clip', label: 'School ID' },
        { action: 'proof', icon: 'fas fa-image', label: 'Proof/Evidence' },
        { action: 'schedule', icon: 'fas fa-calendar', label: 'Schedule' },
        { action: 'push', icon: 'fas fa-check', label: 'Push Examinee', color: 'green' },
        { action: 'cancel', icon: 'fas fa-trash', label: 'Delete', color: 'red' },
    ]);

    const text = $.fn.dataTable.render.text();

    CissCrud.register({
        pageId: 'applicants',
        table: '#applistTable',
        order: [[0, 'asc']],
        ajaxData: AdmissionUi.searchFilters,
        columns: [
            { data: 'admission_id', render: text },
            { data: 'display_name', render: text },
            { data: 'type_name', render: text },
            { data: 'contact', render: text },
            { data: 'registered_at', render: (date, type, row) => type === 'display' ? CissCrud.escapeHtml(row.registered_label) : date },
            { data: 'campus', render: text },
            { data: 'strand', render: text },
            { data: 'schedule_label', render: (label, type) => type === 'display' ? (label ? CissCrud.escapeHtml(label) : '<span class="text-muted">None</span>') : (label || '') },
            { data: 'status', render: (status, type, row) => type === 'display' ? CissCrud.badge(REUPLOAD_BADGES[status] || 'bg-secondary', row.reupload_status) : row.reupload_status },
            { data: null, orderable: false, searchable: false, render: (data, type, row) => row.can_manage ? ACTIONS : '' },
        ],
        actions: Object.assign({}, AdmissionUi.actions, {
            reupload(applicant) {
                const { $modal } = CissCrud.openFor('#reuploadModal', applicant);
                AdmissionUi.showFields($modal, applicant);
                CissCrud.fetchFor('reuploadUrl', applicant.id).done(function (response) {
                    $modal.find('input[name="reuploadallow[]"]').each(function () {
                        this.checked = (response.reuploadallow || []).includes(this.value);
                    });
                    $modal.modal('show');
                });
            },

            push(applicant) {
                AdmissionUi.confirmThen({
                    title: 'Push to Examinee List?',
                    html: CissCrud.escapeHtml(applicant.display_name) + ' will be moved to the examinee list and emailed the exam schedule.',
                    confirmButtonText: 'Yes, push',
                }, () => CissCrud.send('pushUrl', applicant.id));
            },
        }),
    });
})();
