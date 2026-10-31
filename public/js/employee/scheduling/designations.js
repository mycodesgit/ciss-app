/**
 * Scheduling > Faculty Designation (resources/views/pages/employee/scheduling/designations/index.blade.php).
 * Server: FacultyDesignationController. Table, form saving and wire:navigate handling come from CissCrud
 * (public/js/employee/crud.js). The list response also carries the searched campus's faculty, which fill the
 * Add / Edit selects (Select2 to search by name); new designations go to the searched campus and term.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const FORM = 'schedDesignationsSearchForm';
    let searched = {};
    let faculty = [];

    function termLabel() {
        const semester = document.querySelector('#' + FORM + ' select[name="semester"] option[value="' + searched.semester + '"]');
        return semester ? semester.textContent + ' ' + searched.schlyear + ' (' + searched.campus + ')' : '';
    }

    function fillFaculty(form, selected) {
        const select = form.elements.fac_id;
        CissScheduling.fillSelect(select, faculty.map((member) => ({ value: member.id, label: member.name })), '--Select--');
        select.value = selected || '';

        const $select = $(select);
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $select.closest('.modal') });
    }

    CissCrud.register({
        pageId: 'schedDesignations',
        table: '#schedDesignationsTable',
        order: [[0, 'asc']],
        ajaxData: () => (searched = CissScheduling.filters(FORM)),
        columns: [
            { data: 'faculty', render: text },
            { data: 'designation', render: text },
            { data: 'college', render: text },
            { data: 'dunit', render: text },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([
                    { action: 'edit', icon: 'fas fa-pen', label: 'Edit' },
                    { action: 'destroy', icon: 'fas fa-trash', label: 'Delete', color: '#dc3545' },
                ]),
            },
        ],
        tableOptions: {
            language: { emptyTable: 'No faculty designations in this campus and term yet.' },
            drawCallback: function () {
                faculty = (this.api().ajax.json() || {}).faculty || [];
                $('#schedDesignations .js-term-shown').text(termLabel());
            },
        },
        actions: {
            edit(designation) {
                const { $modal, form } = CissCrud.openFor('#editDesignationModal', designation);
                CissCrud.fill(form, designation, ['facCollege', 'designation', 'dunit']);
                fillFaculty(form, designation.fac_id);
                $modal.find('.js-modal-term').text(termLabel());
                $modal.modal('show');
            },

            destroy(designation) {
                Swal.fire({
                    title: 'Delete this designation?',
                    text: designation.faculty + ' - ' + designation.designation + ' will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) CissCrud.send('destroyUrl', designation.id, 'DELETE');
                });
            },
        },
    });

    $(document).on('submit', '#' + FORM, function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    // Add New: empty, for the searched campus and term
    $(document).on('show.bs.modal', '#addDesignationModal', function (e) {
        if (e.target !== this) return;
        const { form } = CissCrud.openFor('#addDesignationModal');
        CissScheduling.copyFilters(form, searched);
        fillFaculty(form);
        $(this).find('.js-modal-term').text(termLabel());
    });
})();
