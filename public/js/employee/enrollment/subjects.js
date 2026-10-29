/**
 * Enrollment > Subjects (resources/views/pages/employee/enrollment/subjects/index.blade.php). Server: SubjectController.
 * The table and the Add / Edit forms (save, toast, reload) come from CissCrud; this file adds the subject code
 * preview and Delete / Activate, which only changes the status (subjects are never removed).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const DETAILS = ['sub_name', 'sub_title', 'sublecredit', 'sublabcredit', 'sub_unit', 'subjweeks', 'subjconthrs',
        'subjlev', 'subdelmod', 'subacadtype', 'subjprereq', 'subjcoreq'];

    CissCrud.register({
        pageId: 'subjects',
        table: '#subjectsTable',
        order: [[0, 'asc']],
        columns: [
            { data: 'sub_code', render: text },
            { data: 'sub_name', render: text },
            { data: 'sub_title', render: text },
            { data: 'sub_unit', render: text },
            {
                data: 'active',
                render: (active, type) => {
                    const label = active ? 'Active' : 'Inactive';
                    return type === 'display' ? CissCrud.badge(active ? 'bg-success' : 'bg-secondary', label) : label;
                },
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (data, type, row) => CissCrud.actionsMenu([
                    { action: 'edit', icon: 'fas fa-pen', label: 'Edit' },
                    row.active
                        ? { action: 'status', icon: 'fas fa-trash', label: 'Delete (set Inactive)', color: '#dc3545' }
                        : { action: 'status', icon: 'fas fa-rotate-left', label: 'Activate', color: 'green' },
                ]),
            },
        ],
        actions: {
            edit(subject) {
                const { $modal, form } = CissCrud.openFor('#editSubjectModal', subject);
                $modal.find('.js-edit-code').val(subject.sub_code);
                $modal.find('.js-edit-college').val(subject.subjcollege);
                $modal.find('.js-edit-department').val(subject.subjdep);
                CissCrud.fill(form, subject, DETAILS);
                $modal.modal('show');
            },

            // Delete only sets the subject Inactive; Activate brings it back
            status(subject) {
                Swal.fire({
                    title: subject.active ? 'Delete this subject?' : 'Activate this subject?',
                    html: '<b>' + CissCrud.escapeHtml(subject.sub_code + ' ' + subject.sub_name) + '</b><br>' +
                        (subject.active
                            ? '<small class="text-muted">It is set Inactive, not removed: its offerings and grades stay. You can activate it again.</small>'
                            : ''),
                    icon: subject.active ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: subject.active ? '#d33' : '#198754',
                    confirmButtonText: subject.active ? 'Yes, set Inactive' : 'Yes, activate',
                }).then((result) => {
                    if (result.isConfirmed) CissCrud.send('statusUrl', subject.id);
                });
            },
        },
    });

    // College + department picked: preview the code the subject will get (the server assigns it when saving)
    $(document).on('change', '#addSubjectForm .js-code-part', function () {
        const form = document.getElementById('addSubjectForm');
        const $preview = $(form).find('.js-code-preview').val('');

        if (!form.elements.college.value || !form.elements.deptCod.value) return;

        $.get(document.getElementById('subjects').dataset.nextCodeUrl, { college: form.elements.college.value, deptCod: form.elements.deptCod.value })
            .done((response) => $preview.val(response.code))
            .fail(() => toastr.error('Could not get the next subject code.'));
    });
})();
