/**
 * Queue > Dashboard (resources/views/pages/employee/queue/index.blade.php). Server: QueueDashboardController::data
 * (QueueBoard::snapshot). Polls every few seconds while the dashboard is open; with wire:navigate the timer starts
 * on every livewire:navigated that lands on the dashboard and stops when leaving.
 */
(function () {
    'use strict';

    const POLL_MS = 5000;
    const STATUS_BADGES = { 1: 'bg-secondary', 2: 'bg-success', 3: 'bg-warning text-dark' };
    const escape = (text) => CissCrud.escapeHtml(text);

    let timer = null;

    document.addEventListener('livewire:navigated', start);
    document.addEventListener('livewire:navigating', stop);

    function start() {
        stop();
        if (!document.getElementById('queueDashboard')) return;
        refresh();
        timer = setInterval(refresh, POLL_MS);
    }

    function stop() {
        if (timer) clearInterval(timer);
        timer = null;
    }

    function refresh() {
        const page = document.getElementById('queueDashboard');
        if (!page) return stop();

        $.get(page.dataset.url).done(render).fail(() => $(page).find('.js-updated').text('(could not refresh)'));
    }

    function counterCard(counter) {
        return '<div class="col-xl-3 col-md-4 col-6">' +
            '<div class="border rounded-3 p-3 h-100' + (counter.status === 2 ? ' border-success' : '') + '">' +
            '<div class="d-flex justify-content-between align-items-start mb-2">' +
            '<span class="fw-bold">Window ' + escape(counter.window) + '</span>' +
            CissCrud.badge(STATUS_BADGES[counter.status] || 'bg-secondary', counter.status_label) +
            '</div>' +
            '<small class="text-uppercase text-muted d-block" style="font-size: .65rem;">Serving No.</small>' +
            '<div class="fs-4 fw-bolder lh-1 mb-2">' + escape(counter.serving || '---') + '</div>' +
            '<small class="d-block text-muted">' + escape(counter.category || '') + '</small>' +
            '<small class="d-block"><i class="fas fa-user me-1 text-muted"></i>' + escape(counter.staff || 'Unassigned') + '</small>' +
            '<small class="d-block text-muted">Handled today: <strong>' + counter.handled_today + '</strong></small>' +
            '</div></div>';
    }

    function render(data) {
        const $page = $('#queueDashboard');

        Object.entries(data.totals).forEach(([key, value]) => $page.find('.js-total-' + key).text(value));
        $page.find('.js-queue-mode').attr('class', 'badge js-queue-mode ' + (data.on ? 'bg-success' : 'bg-danger')).text('Queueing ' + (data.on ? 'On' : 'Off'));
        $page.find('.js-updated').text(data.time);

        $page.find('.js-counters').html(data.counters.length
            ? data.counters.map(counterCard).join('')
            : '<div class="col-12 text-center text-muted py-4"><i class="fas fa-desktop d-block fs-3 mb-2 opacity-25"></i>No counters in this campus yet. Add them in Counters.</div>');

        $page.find('.js-next-in-line').html(data.nextInLine.length
            ? data.nextInLine.map((row) => '<li class="list-group-item px-0 d-flex justify-content-between align-items-center">' +
                '<div><strong>' + escape(row.number || '-') + '</strong><br><small class="text-muted">' + escape(row.category) + '</small></div>' +
                CissCrud.badge('bg-light text-dark', row.waiting + ' waiting') + '</li>').join('')
            : '<li class="list-group-item px-0 text-center text-muted">No numbers waiting.</li>');
    }
})();
