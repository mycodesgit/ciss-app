/**
 * Scheduling > Class Rooms (resources/views/pages/employee/scheduling/rooms/index.blade.php). Server: RoomController.
 * Table, form saving and wire:navigate handling come from CissCrud (public/js/employee/crud.js); searching another
 * campus reloads the table. New rooms go to the searched campus.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const FORM = 'schedRoomsSearchForm';
    let searched = {}; // filters of the rows on screen

    CissCrud.register({
        pageId: 'schedRooms',
        table: '#schedRoomsTable',
        order: [[1, 'asc']],
        ajaxData: () => (searched = CissScheduling.filters(FORM)),
        columns: [
            { data: 'college', render: text },
            { data: 'room_name', render: text },
            { data: 'room_capacity', render: text },
            {
                data: 'status_label',
                render: (label, type, room) => type === 'display'
                    ? CissCrud.badge(String(room.status) === '1' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger', label)
                    : label,
            },
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
        tableOptions: { language: { emptyTable: 'No rooms in this campus yet.' } },
        actions: {
            edit(room) {
                const { $modal, form } = CissCrud.openFor('#editRoomModal', room);
                CissCrud.fill(form, room, ['college_room', 'room_name', 'room_capacity', 'status']);
                $modal.modal('show');
            },

            destroy(room) {
                Swal.fire({
                    title: 'Delete this room?',
                    text: room.room_name + ' will be removed. A room used by class schedules cannot be deleted; set it Inactive instead.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) CissCrud.send('destroyUrl', room.id, 'DELETE');
                });
            },
        },
    });

    $(document).on('submit', '#' + FORM, function (e) {
        e.preventDefault();
        CissCrud.reload();
    });

    // Add New: empty, for the searched campus
    $(document).on('show.bs.modal', '#addRoomModal', function (e) {
        if (e.target !== this) return;
        const { form } = CissCrud.openFor('#addRoomModal');
        CissScheduling.copyFilters(form, searched);
    });
})();
