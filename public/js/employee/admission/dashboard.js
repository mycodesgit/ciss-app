/**
 * Admission > Dashboard (resources/views/pages/employee/admission/index.blade.php).
 * Pushed to <head>, so with wire:navigate it loads once: init() runs on every livewire:navigated
 * that lands on the dashboard, and the chart is destroyed when leaving. Uses Chart.js v2.
 */
(function () {
    'use strict';

    let chart = null;

    document.addEventListener('livewire:navigated', init);
    document.addEventListener('livewire:navigating', function () {
        if (chart) {
            chart.destroy();
            chart = null;
        }
    });

    function init() {
        const canvas = document.getElementById('admission-chart');
        if (!canvas) return;

        renderCalendar();

        $.get(canvas.dataset.url)
            .done(function (data) {
                chart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Registered Applicant: No schedule yet', backgroundColor: '#90ee90', data: data.registered },
                            { label: 'Registered Applicant: Scheduled', backgroundColor: '#00a65a', data: data.scheduled },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false, // fill the 320px box in the view instead of keeping a width:height ratio
                        legend: { display: false }, // the legend is in the card header
                        tooltips: { mode: 'index', intersect: false },
                        scales: {
                            xAxes: [{ gridLines: { display: true } }],
                            yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }],
                        },
                    },
                });
            })
            .fail(function () {
                toastr.error('Could not load the applicant chart.');
            });
    }

    // Month grid for the "Calendar" card; today is highlighted
    function renderCalendar() {
        const container = document.getElementById('calendarDaysContainer');
        if (!container) return;

        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth();
        const firstWeekday = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        document.getElementById('calendarMonthYear').textContent =
            today.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

        const cells = [];
        for (let i = firstWeekday - 1; i >= 0; i--) cells.push({ day: daysInPrevMonth - i, muted: true });
        for (let day = 1; day <= daysInMonth; day++) cells.push({ day, active: day === today.getDate() });
        for (let day = 1; cells.length % 7 !== 0; day++) cells.push({ day, muted: true });

        let html = '';
        for (let i = 0; i < cells.length; i += 7) {
            html += '<div class="row g-1 text-center mb-1">' + cells.slice(i, i + 7).map(function (cell) {
                const classes = 'shadcn-calendar-day' + (cell.muted ? ' muted' : '') + (cell.active ? ' active' : '');
                return '<div class="col"><div class="' + classes + '">' + cell.day + '</div></div>';
            }).join('') + '</div>';
        }

        container.innerHTML = html;
    }
})();
