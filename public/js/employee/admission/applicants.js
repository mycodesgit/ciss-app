/**
 * Admission > List of Applicant (resources/views/pages/employee/admission/applicant/list.blade.php).
 * Table, modal-form saving and wire:navigate handling come from public/js/employee/crud.js.
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

    // Current values of the search form, sent with every table load
    function searchFilters() {
        const form = document.getElementById('applicantSearchForm');
        return {
            year: form.elements.year.value,
            campus: form.elements.campus.value,
            strand: form.elements.strand.value,
        };
    }

    // Fill read-only fields marked data-show="field" inside a modal
    function showFields($modal, applicant) {
        $modal.find('[data-show]').each(function () {
            this.value = applicant[this.dataset.show] || this.dataset.empty || '';
        });
    }

    function openDocument(title, url, note) {
        const $modal = $('#documentModal');
        const isPdf = url && /\.pdf$/i.test(url);

        $modal.find('.modal-title').text(title);
        $modal.find('.js-document-note').text(note || '');
        $modal.find('.js-document-image').toggleClass('d-none', !url || isPdf).attr('src', url && !isPdf ? url : '');
        $modal.find('.js-document-link').toggleClass('d-none', !isPdf).attr('href', isPdf ? url : '#');
        $modal.find('.js-document-empty').toggleClass('d-none', !!url);
        $modal.modal('show');
    }

    function confirmThen(options, onConfirm) {
        Swal.fire(Object.assign({
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
        }, options)).then((result) => {
            if (result.isConfirmed) onConfirm();
        });
    }

    CissCrud.register({
        pageId: 'applicants',
        table: '#applistTable',
        order: [[0, 'asc']],
        ajaxData: searchFilters,
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
        actions: {
            view(applicant) {
                const { $modal, form } = CissCrud.openFor('#applicantInfoModal', applicant);
                showFields($modal, applicant);
                CissCrud.fill(form, applicant, ['fname', 'mname', 'lname', 'ext', 'gender', 'bday', 'civil_status', 'address']);
                $modal.modal('show');
            },

            reupload(applicant) {
                const { $modal } = CissCrud.openFor('#reuploadModal', applicant);
                showFields($modal, applicant);
                CissCrud.fetchFor('reuploadUrl', applicant.id).done(function (response) {
                    $modal.find('input[name="reuploadallow[]"]').each(function () {
                        this.checked = (response.reuploadallow || []).includes(this.value);
                    });
                    $modal.modal('show');
                });
            },

            schoolId(applicant) {
                openDocument('School ID / Valid ID - ' + applicant.display_name, applicant.school_id_url);
            },

            proof(applicant) {
                openDocument('Proof/Evidence - ' + applicant.display_name, applicant.proof_url, applicant.proof_type ? 'Type: ' + applicant.proof_type : '');
            },

            schedule(applicant) {
                const { $modal, form } = CissCrud.openFor('#scheduleModal', applicant);
                showFields($modal, applicant);

                // Full schedules can't be picked, except the one the applicant already has
                $(form.elements.dateID).find('option[data-full="1"]').each(function () {
                    this.disabled = String(this.value) !== String(applicant.dateID);
                });
                form.elements.dateID.value = applicant.dateID || '';
                form.elements.venue.value = applicant.venue || '';
                $modal.modal('show');
            },

            push(applicant) {
                confirmThen({
                    title: 'Push to Examinee List?',
                    html: CissCrud.escapeHtml(applicant.display_name) + ' will be moved to the examinee list and emailed the exam schedule.',
                    confirmButtonText: 'Yes, push',
                }, () => CissCrud.send('pushUrl', applicant.id));
            },

            cancel(applicant) {
                confirmThen({
                    title: 'Remove this applicant?',
                    html: CissCrud.escapeHtml(applicant.display_name) + ' will be removed from the applicant list.',
                    confirmButtonText: 'Yes, remove',
                    confirmButtonColor: '#dc3545',
                }, () => CissCrud.send('cancelUrl', applicant.id));
            },
        },
    });

    // Search: reload the table with the new filters (no page reload)
    $(document).on('submit', '#applicantSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
