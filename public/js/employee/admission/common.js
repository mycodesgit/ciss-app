/**
 * Shared by the admission list pages (applicants.js, examinees.js): search form, the shared modals
 * (View Data, Schedule, Documents) and the Delete confirmation. Uses CissCrud (public/js/employee/crud.js).
 */
window.AdmissionUi = (function () {
    'use strict';

    // Current values of the search form, sent with every table load
    function searchFilters() {
        const form = document.getElementById('admissionSearchForm');
        return {
            year: form.elements.year.value,
            campus: form.elements.campus.value,
            strand: form.elements.strand.value,
        };
    }

    // Fill read-only fields marked data-show="field" inside a modal
    function showFields($modal, row) {
        $modal.find('[data-show]').each(function () {
            this.value = row[this.dataset.show] || this.dataset.empty || '';
        });
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

    // Document modal: image, or a link for PDFs, or "No document uploaded"
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

    // Row actions used by both pages (spread into a page's CissCrud actions)
    const actions = {
        view(row) {
            const { $modal, form } = CissCrud.openFor('#applicantInfoModal', row);
            showFields($modal, row);
            CissCrud.fill(form, row, ['fname', 'mname', 'lname', 'ext', 'gender', 'bday', 'civil_status', 'address']);
            $modal.modal('show');
        },

        schedule(row) {
            const { $modal, form } = CissCrud.openFor('#scheduleModal', row);
            showFields($modal, row);

            // Full schedules can't be picked, except the one the applicant already has
            $(form.elements.dateID).find('option[data-full="1"]').each(function () {
                this.disabled = String(this.value) !== String(row.dateID);
            });
            form.elements.dateID.value = row.dateID || '';
            form.elements.venue.value = row.venue || '';
            $modal.modal('show');
        },

        schoolId(row) {
            openDocument('School ID / Valid ID - ' + row.display_name, row.school_id_url);
        },

        reportCard(row) {
            openDocument('Report Card / TOR - ' + row.display_name, row.report_card_url);
        },

        proof(row) {
            openDocument('Proof/Evidence - ' + row.display_name, row.proof_url, row.proof_type ? 'Type: ' + row.proof_type : '');
        },

        cancel(row) {
            confirmThen({
                title: 'Remove this applicant?',
                html: CissCrud.escapeHtml(row.display_name) + ' will be removed from the list.',
                confirmButtonText: 'Yes, remove',
                confirmButtonColor: '#dc3545',
            }, () => CissCrud.send('cancelUrl', row.id));
        },
    };

    // Search: reload the table with the new filters (no page reload)
    $(document).on('submit', '#admissionSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    return { searchFilters, showFields, confirmThen, openDocument, actions };
})();
