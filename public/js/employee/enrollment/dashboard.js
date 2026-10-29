/**
 * Enrollment > Dashboard (resources/views/pages/employee/enrollment/index.blade.php).
 * Pushed to <head>, so with wire:navigate it loads once: init() runs on every livewire:navigated that lands
 * on the dashboard, and the charts are destroyed when leaving. Uses Chart.js v2.
 * Charts whose canvas is not on the page (e.g. colleges for graduate school staff) are skipped.
 */
(function () {
    'use strict';

    // Bar colors, repeated when there are more bars than colors
    const PALETTE = ['#108d6d', '#2ecc71', '#3498db', '#f1c40f', '#e67e22', '#9b59b6', '#e74c3c', '#1abc9c', '#34495e', '#16a085', '#d35400', '#8e44ad'];
    const colorsFor = (count) => Array.from({ length: count }, (_, i) => PALETTE[i % PALETTE.length]);

    let charts = [];

    document.addEventListener('livewire:navigated', init);
    document.addEventListener('livewire:navigating', function () {
        charts.forEach((chart) => chart.destroy());
        charts = [];
    });

    // Student list cards: open the group's PDF in the modal
    $(document).on('click', '#enrollmentDashboard .js-student-list', function () {
        const page = document.getElementById('enrollmentDashboard');
        const url = page.dataset.pdfUrl.replace('__GROUP__', this.dataset.group);

        CissCrud.openPdf('#studentsPdfModal', '', url, this.dataset.title);
    });

    function init() {
        const page = document.getElementById('enrollmentDashboard');
        if (!page || !document.getElementById('programChart')) return; // no active term: no charts

        $.get(page.dataset.chartsUrl)
            .done(render)
            .fail(() => toastr.error('Could not load the enrollment charts.'));
    }

    function render(data) {
        const enrolledIn = (term) => 'No. of Students Enrolled in ' + term;

        if (!data.graduate) {
            bar('previousCollegeChart', {
                label: enrolledIn(data.terms.previous),
                labels: data.colleges.previous.map((college) => college.label),
                counts: data.colleges.previous.map((college) => college.count),
                colors: data.colleges.previous.map((college) => college.color),
            });
            bar('currentCollegeChart', {
                label: enrolledIn(data.terms.current),
                labels: data.colleges.current.map((college) => college.label),
                counts: data.colleges.current.map((college) => college.count),
                colors: data.colleges.current.map((college) => college.color),
            });
            bar('campusChart', {
                label: enrolledIn(data.terms.current),
                labels: data.campuses.labels,
                counts: data.campuses.counts,
            });
        }

        bar('previousLevelChart', { label: enrolledIn(data.terms.previous), labels: data.yearLevels.labels, counts: data.yearLevels.previous });
        bar('currentLevelChart', { label: enrolledIn(data.terms.current), labels: data.yearLevels.labels, counts: data.yearLevels.current });
        bar('programChart', { label: enrolledIn(data.terms.current) + ' per Program', labels: data.programs.labels, counts: data.programs.counts });
    }

    // One bar chart: { label, labels, counts, colors? }
    function bar(canvasId, chart) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        charts.push(new Chart(canvas, {
            type: 'bar',
            data: {
                labels: chart.labels,
                datasets: [{
                    label: chart.label,
                    data: chart.counts,
                    backgroundColor: chart.colors || colorsFor(chart.counts.length),
                }],
            },
            options: {
                maintainAspectRatio: false, // fill the 330px .chart-box
                scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] },
            },
        }));
    }
})();
