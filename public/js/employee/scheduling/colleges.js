/**
 * Scheduling > Colleges (resources/views/pages/employee/scheduling/colleges/index.blade.php). Server: CollegeController.
 * Table, Edit form saving and wire:navigate handling come from CissCrud (public/js/employee/crud.js).
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();

    CissCrud.register({
        pageId: 'schedColleges',
        table: '#schedCollegesTable',
        order: [[1, 'asc']],
        columns: [
            { data: 'college_abbr', render: text },
            { data: 'college_name', render: text },
            { data: 'campuses', render: (codes) => CissScheduling.campusLabel(codes) },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: () => CissCrud.actionsMenu([{ action: 'edit', icon: 'fas fa-pen', label: 'Edit Campuses' }]),
            },
        ],
        actions: {
            edit(college) {
                const { $modal, form } = CissCrud.openFor('#editCollegeModal', college);
                $modal.find('.js-college-abbr').val(college.college_abbr);
                $modal.find('.js-college-name').val(college.college_name);
                CissScheduling.checkCampuses(form, college.campuses);
                $modal.modal('show');
            },
        },
    });
})();
