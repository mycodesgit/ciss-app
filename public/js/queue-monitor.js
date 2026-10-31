/**
 * Public Queue Monitor (resources/views/pages/queue-monitor.blade.php). Server: QueueMonitorController::data.
 * Polls every 2 seconds. After Start (browsers play sound only after a click) it announces, with a chime and a
 * voice: a window taking a new number (Next) and a number being called again (Call). No jQuery: a plain page.
 */
(function () {
    'use strict';

    const POLL_MS = 2000;
    const page = document.getElementById('queueMonitor');
    const $ = (selector) => page.querySelector(selector);

    let started = false;
    let previous = null; // the last data, to find what changed

    function escape(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    // "ENRO-0004" of Enrollment -> "Enrollment number 4" (easier to understand than letters read one by one)
    function spoken(number, category) {
        const digits = String(number).match(/(\d+)$/);
        return digits && category ? category + ' number ' + parseInt(digits[1], 10) : String(number).replace(/-/g, ' ');
    }

    // Announcements play one after another (the voice queue is not cancelled)
    function announce(text) {
        if (!started) return;
        new Audio(page.dataset.sound).play().catch(() => {});
        if ('speechSynthesis' in window) {
            const speech = new SpeechSynthesisUtterance(text);
            speech.lang = 'en-US';
            speech.rate = 0.9;
            setTimeout(() => window.speechSynthesis.speak(speech), 1200); // after the chime
        }
    }

    function flash(selector) {
        const box = $(selector);
        box.classList.remove('qm-flash');
        void box.offsetWidth; // restart the animation
        box.classList.add('qm-flash');
    }

    function render(data) {
        $('.js-off').classList.toggle('d-none', data.on);

        $('.js-serving').textContent = data.nowServing ? data.nowServing.number : '-';
        $('.js-serving-window').textContent = data.nowServing ? 'Proceed to Window ' + data.nowServing.window : ' ';
        $('.js-calling').textContent = data.calling ? data.calling.number : '-';
        $('.js-calling-window').textContent = data.calling ? 'Proceed to Window ' + data.calling.window : ' ';

        $('.js-counters').innerHTML = data.counters.length
            ? data.counters.map((counter) => '<div class="col-6"><div class="qm-counter' + (counter.status === 2 ? ' open' : '') + '">' +
                '<div class="d-flex justify-content-between"><strong>Window ' + escape(counter.window) + '</strong>' +
                '<small class="text-muted">' + escape(counter.status_label) + '</small></div>' +
                '<div class="number">' + escape(counter.serving || '---') + '</div>' +
                '<small class="text-muted">' + escape(counter.category || '') + '</small>' +
                '</div></div>').join('')
            : '<div class="col-12 text-muted">No windows yet.</div>';

        const next = data.nextInLine.filter((row) => row.number).map((row) => row.category + ': next is ' + row.number + ' (' + row.waiting + ' waiting)');
        $('.js-ticker').textContent = next.length
            ? 'Welcome to Central Philippines State University.   •   ' + next.join('   •   ')
            : 'Welcome to Central Philippines State University. Please wait for your number to be called.';
    }

    // What changed since the last poll: windows with a new number, and a new Call
    function announceChanges(data) {
        if (!previous) return;

        const before = Object.fromEntries(previous.counters.map((counter) => [counter.window, counter.serving]));
        data.counters.forEach(function (counter) {
            if (counter.serving && counter.serving !== before[counter.window]) {
                announce(spoken(counter.serving, counter.category) + '. Please proceed to window ' + counter.window + '.');
                flash('.js-serving-box');
            }
        });

        const call = data.calling ? data.calling.window + '|' + data.calling.number : null;
        const callBefore = previous.calling ? previous.calling.window + '|' + previous.calling.number : null;
        if (call && call !== callBefore) {
            const counter = data.counters.find((row) => row.window === data.calling.window) || {};
            announce('Calling ' + spoken(data.calling.number, counter.category) + '. Please proceed to window ' + data.calling.window + '.');
            flash('.js-calling-box');
        }
    }

    function poll() {
        fetch(page.dataset.url, { headers: { Accept: 'application/json' } })
            .then((response) => response.ok ? response.json() : Promise.reject(response.status))
            .then(function (data) {
                render(data);
                announceChanges(data);
                previous = data;
            })
            .catch(() => {}) // keep the last screen; the next poll tries again
            .finally(() => setTimeout(poll, POLL_MS));
    }

    function clock() {
        const now = new Date();
        $('.js-clock').textContent = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        $('.js-date').textContent = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
    }

    $('.js-start-button').addEventListener('click', function () {
        started = true;
        $('.js-start').classList.add('d-none');
        new Audio(page.dataset.sound).play().catch(() => {}); // unlocks sound for this page
        if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen().catch(() => {});
    });

    clock();
    setInterval(clock, 1000);
    poll();
})();
