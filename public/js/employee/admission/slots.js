/**
 * Admission > Availability Slots (resources/views/pages/employee/admission/configure/slotsview.blade.php).
 * Pushed to <head>, so with wire:navigate it loads once: init() runs on every livewire:navigated
 * that lands on the page, and the auto-refresh stops when leaving.
 */
(function () {
    'use strict';

    const REFRESH_MS = 30000;
    let timer = null;

    document.addEventListener('livewire:navigated', init);
    document.addEventListener('livewire:navigating', stopRefresh);

    function init() {
        const form = document.getElementById('slotsFilterForm');
        if (!form) return;

        form.addEventListener('submit', (event) => event.preventDefault());
        form.addEventListener('change', () => load());

        load();
        stopRefresh();
        timer = setInterval(() => load(true), REFRESH_MS);
    }

    function stopRefresh() {
        clearInterval(timer);
        timer = null;
    }

    // Loads partials/slot-cards.blade.php into #slot-container. quiet = auto-refresh: no dimming, no error toast
    function load(quiet) {
        const page = document.getElementById('slots');
        if (!page) return stopRefresh();

        const container = document.getElementById('slot-container');
        if (!quiet) container.classList.add('is-loading');

        $.get(page.dataset.listUrl, $('#slotsFilterForm').serialize())
            .done((html) => { container.innerHTML = html; })
            .fail(() => { if (!quiet) toastr.error('Could not load the availability slots.'); })
            .always(() => container.classList.remove('is-loading'));
    }
})();
