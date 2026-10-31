/**
 * Kiosk > Dashboard (resources/views/pages/employee/kiosk/index.blade.php). Server: KioskDashboardController.
 * Pushed to <head>, so with wire:navigate it loads once: init() runs on every livewire:navigated that lands on
 * the dashboard, and the chart is destroyed when leaving. Uses Chart.js v2.
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
        const canvas = document.getElementById('kioskLogsChart');
        if (!canvas) return;

        chart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{ label: 'Kiosk Logs', backgroundColor: '#00a65a', data: JSON.parse(canvas.dataset.monthly || '[]') }],
            },
            options: {
                maintainAspectRatio: false, // fill the 250px box in the view
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }],
                },
            },
        });
    }
})();
