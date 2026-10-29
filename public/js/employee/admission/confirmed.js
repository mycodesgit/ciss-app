/**
 * Admission > Confirmed Applicants (resources/views/pages/employee/admission/confirm/list.blade.php).
 * Shared admission actions come from admission/common.js (AdmissionUi); table, modal-form saving
 * and wire:navigate handling from public/js/employee/crud.js (CissCrud).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();

    // Guidance users (data-can-record="0") only view; the server checks this too
    function canRecord() {
        return document.getElementById('confirmed').dataset.canRecord === '1';
    }

    function actionsFor(row) {
        return CissCrud.actionsMenu([
            { action: 'view', icon: 'fas fa-eye', label: 'View Data' },
            { action: 'preEnrollment', icon: 'fas fa-file-pdf', label: 'Generate Pre-Enrollment' },
            { action: 'interview', icon: 'fas fa-file-lines', label: canRecord() ? 'Assign Interview Result' : 'View Interview Result' },
            { action: 'push', icon: 'fas fa-check', label: 'Push to Accepted Applicant', color: 'green', disabled: !canRecord() || !row.interview_completed },
        ]);
    }

    function setField(form, name, value) {
        const field = form.querySelector('[name="' + name + '"]');
        if (field) field.value = value ?? '';
    }

    CissCrud.register({
        pageId: 'confirmed',
        table: '#confrmlistTable',
        order: [[0, 'asc']],
        ajaxData: AdmissionUi.searchFilters,
        columns: [
            { data: 'admission_id', render: text },
            { data: 'display_name', render: text },
            { data: 'type_name', render: text },
            { data: 'contact', render: text },
            {
                data: 'interview_remarks_name',
                render: (label, type, row) => type === 'display' ? CissCrud.badge(row.interview_completed ? 'bg-success' : 'bg-danger', label) : label,
            },
            { data: 'schedule_at', render: (at, type, row) => type === 'display' ? CissCrud.escapeHtml(row.schedule_label || '') : at },
            { data: 'campus', render: text },
            { data: 'strand', render: text },
            { data: null, orderable: false, searchable: false, render: (data, type, row) => row.can_manage ? actionsFor(row) : '' },
        ],
        actions: Object.assign({}, AdmissionUi.actions, {
            interview(applicant) {
                const { $modal, form } = CissCrud.openFor('#interviewModal', applicant);
                AdmissionUi.showFields($modal, applicant);

                setField(form, 'rating', applicant.rating);
                setField(form, 'remarks', applicant.interview_remarks);
                setField(form, 'course', applicant.interview_course || applicant.preference_1); // default: 1st preference
                setField(form, 'reason', applicant.interview_reason);
                $modal.modal('show');
            },

            push(applicant) {
                AdmissionUi.confirmThen({
                    title: 'Push to Accepted Applicants?',
                    html: CissCrud.escapeHtml(applicant.display_name) + ' will be moved to the Accepted Applicants list.',
                    confirmButtonText: 'Yes, push',
                }, () => CissCrud.send('pushUrl', applicant.id));
            },
        }),
    });
})();
