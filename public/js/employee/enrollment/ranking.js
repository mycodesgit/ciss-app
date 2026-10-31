/**
 * Enrollment > Ranking (resources/views/pages/employee/enrollment/ranking/index.blade.php).
 * Server: RankingController (already ranked: best GWA first, no GWA last). The table and wire:navigate handling
 * come from CissCrud (public/js/employee/crud.js), the Course select from course-select.js; searching reloads the table.
 */
(function () {
    'use strict';

    const text = $.fn.dataTable.render.text();
    const form = () => document.getElementById('rankingSearchForm');

    // Filters of the last table load, so the export title matches the rows on screen
    let shownFilters = {};
    function loadFilters() {
        const elements = form().elements;
        shownFilters = { campus: elements.campus.value, schlyear: elements.schlyear.value, semester: elements.semester.value, progCod: elements.progCod.value };
        return shownFilters;
    }

    function shownSearch() {
        if (!shownFilters.progCod) return '';
        const elements = form().elements;
        const semester = elements.semester.querySelector('option[value="' + shownFilters.semester + '"]');
        const course = elements.progCod.querySelector('option[value="' + CSS.escape(shownFilters.progCod) + '"]');
        return (course ? course.textContent : shownFilters.progCod) + ' - ' + (semester ? semester.textContent : '') + ' ' + shownFilters.schlyear + ' (' + shownFilters.campus + ')';
    }

    // Unranked rows (no GWA) sort after every rank
    const rankSort = (rank, type) => type === 'sort' || type === 'type' ? (rank === null ? Number.MAX_SAFE_INTEGER : rank) : (rank === null ? '-' : rank);

    CissCrud.register({
        pageId: 'ranking',
        table: '#rankingTable',
        order: [[0, 'asc']],
        ajaxData: loadFilters,
        columns: [
            { data: 'rank', render: (rank, type) => type === 'display' && rank !== null ? '<strong>' + rank + '</strong>' : rankSort(rank, type) },
            { data: 'studentID', render: text },
            { data: 'name', render: text },
            { data: 'section', render: text },
            { data: null, render: (row) => row.graded + ' / ' + row.subjects },
            { data: 'gwa', render: (gwa, type) => gwa === null ? (type === 'sort' ? Number.MAX_SAFE_INTEGER : '-') : (type === 'display' ? Number(gwa).toFixed(4) : gwa) },
        ],
        tableOptions: {
            language: { emptyTable: 'No students to show. Select a campus, school year, semester and course, then click Search.' },
            pageLength: 50,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: () => 'Ranking - ' + shownSearch(),
            }],
            drawCallback: () => $('#ranking .js-search-shown').text(shownSearch()),
        },
    });

    $(document).on('submit', '#rankingSearchForm', function (e) {
        e.preventDefault();
        CissCrud.reload();
    });
})();
