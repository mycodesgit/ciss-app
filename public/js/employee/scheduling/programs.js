/**
 * Scheduling > Programs (resources/views/pages/employee/scheduling/programs/index.blade.php). Server: ProgramController.
 * Table, Edit form saving and wire:navigate handling come from CissCrud (public/js/employee/crud.js).
 * The program code is shown only: the server makes a new one when the college or department changes.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const page = () => document.getElementById('schedPrograms');
    let editing = null; // the program open in the Edit modal

    CissCrud.register({
        pageId: 'schedPrograms',
        table: '#schedProgramsTable',
        order: [[1, 'asc']],
        columns: [
            { data: 'progCod', render: text },
            { data: 'progAcronym', render: text },
            { data: 'progName', render: text },
            { data: 'campuses', render: (codes) => CissScheduling.campusLabel(codes) },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([{ action: 'edit', icon: 'fas fa-pen', label: 'Edit' }]),
            },
        ],
        actions: {
            edit(program) {
                editing = program;
                const { $modal, form } = CissCrud.openFor('#editProgramModal', program);
                CissCrud.fill(form, program, ['progCollege', 'progName', 'progAcronym', 'progLev']);
                CissScheduling.checkCampuses(form, program.campuses);
                $modal.find('.js-code').val(program.progCod);
                $modal.find('.js-code-note').text('');
                CissScheduling.departments(form.elements.progDep, page().dataset.departmentsUrl, program.progCollege, program.progDep);
                $modal.modal('show');
            },
        },
    });

    // Another college: its departments; another college or department: the code will be renumbered on save
    $(document).on('change', '#editProgramForm .js-college', function () {
        CissScheduling.departments(this.form.elements.progDep, page().dataset.departmentsUrl, this.value);
    });

    $(document).on('change', '#editProgramForm .js-college, #editProgramForm .js-department', function () {
        const form = this.form;
        const moved = editing && (form.elements.progCollege.value !== editing.progCollege || form.elements.progDep.value !== editing.progDep);
        $(form).find('.js-code-note').text(moved ? 'A new program code is made when you save.' : '');
    });
})();
