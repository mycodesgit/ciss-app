<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Public Queue Monitor (TV screen), no login. QueueMonitorController; polled by public/js/queue-monitor.js --}}
    <title>Queue Monitor - {{ $campusName }}</title>
    <link rel="shortcut icon" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/fontawesome-free-V6/css/all.min.css') }}">
    <style>
        :root { --qm-green: #04401f; --qm-light: #e8f5ec; }
        html, body, #queueMonitor { height: 100%; }
        body { background: #f4f6f5; font-family: Arial, sans-serif; overflow: hidden; }

        .qm-top { background: var(--qm-green); color: #fff; padding: .75rem 1.5rem; }
        .qm-top img { width: 46px; }
        .qm-clock { font-size: 1.6rem; font-weight: bold; line-height: 1; }

        .qm-main { height: calc(100% - 72px - 52px); padding: 1.25rem; }
        .qm-panel { background: #fff; border-radius: 1rem; box-shadow: 0 4px 14px rgba(0, 0, 0, .08); height: 100%; }

        .qm-label { letter-spacing: 2px; font-weight: bold; color: #6c757d; text-transform: uppercase; }
        /* One line, sized for the longest numbers (PRINT-0001) in the left panel */
        .qm-serving { font-size: clamp(3rem, 7.5vw, 9rem); font-weight: 900; color: #198754; line-height: 1.1; white-space: nowrap; }
        .qm-calling { font-size: clamp(2.5rem, 5.5vw, 6.5rem); font-weight: 900; color: #212529; line-height: 1.1; white-space: nowrap; }
        .qm-window { font-size: clamp(1.4rem, 2.6vw, 2.4rem); font-weight: bold; }
        .qm-flash { animation: qm-flash 1s ease-in-out 4; }
        @keyframes qm-flash { 50% { background: #fff3cd; } }

        .qm-counters { overflow-y: auto; }
        .qm-counter { border-radius: .75rem; border: 2px solid #dee2e6; padding: .6rem .9rem; }
        .qm-counter.open { border-color: #198754; background: var(--qm-light); }
        .qm-counter .number { font-size: clamp(1.4rem, 2.4vw, 2.2rem); font-weight: 900; }

        .qm-bottom { background: #fff; border-top: 1px solid #dee2e6; height: 52px; overflow: hidden; white-space: nowrap; }
        .qm-ticker { display: inline-block; padding-left: 100%; animation: qm-ticker 30s linear infinite; line-height: 52px; font-size: 1.15rem; }
        @keyframes qm-ticker { to { transform: translateX(-100%); } }

        /* Click to start (browsers allow sound only after a click) and Queueing Off */
        .qm-overlay { position: fixed; inset: 0; background: rgba(4, 64, 31, .92); color: #fff; z-index: 10; display: flex; align-items: center; justify-content: center; text-align: center; }
    </style>
</head>
<body>
    <div id="queueMonitor" data-url="{{ route('queue-monitor.data', $campus) }}" data-sound="{{ asset('uilibs/sound/announcement-sound-effect.wav') }}">
        <div class="qm-top d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('uilibs/images/cpsulogov4.png') }}" alt="">
                <div>
                    <div class="fw-bold fs-4 lh-1">CPSU Queueing System</div>
                    <small>{{ $campusName }} &middot; Registrar</small>
                </div>
            </div>
            <div class="text-end">
                <div class="qm-clock js-clock">--:--</div>
                <small class="js-date"></small>
            </div>
        </div>

        <div class="qm-main">
            <div class="row g-3 h-100">
                <div class="col-7 h-100">
                    <div class="qm-panel d-flex flex-column justify-content-around text-center p-4">
                        <div class="js-serving-box rounded-4 py-3">
                            <div class="qm-label">Now Serving</div>
                            <div class="qm-serving js-serving">-</div>
                            <div class="qm-window text-success js-serving-window">&nbsp;</div>
                        </div>
                        <hr class="my-0">
                        <div class="js-calling-box rounded-4 py-3">
                            <div class="qm-label">Calling</div>
                            <div class="qm-calling js-calling">-</div>
                            <div class="qm-window js-calling-window">&nbsp;</div>
                        </div>
                    </div>
                </div>
                <div class="col-5 h-100">
                    <div class="qm-panel p-3 d-flex flex-column">
                        <div class="qm-label mb-2">Windows</div>
                        <div class="qm-counters flex-grow-1">
                            <div class="row g-2 js-counters"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="qm-bottom">
            <span class="qm-ticker js-ticker">Welcome to Central Philippines State University. Please wait for your number to be called.</span>
        </div>

        <div class="qm-overlay js-start">
            <div>
                <i class="fas fa-volume-high fa-3x mb-3"></i>
                <h1 class="fw-bold">Queue Monitor</h1>
                <p class="fs-5 mb-4">Click to start the display with sound and voice announcements.</p>
                <button type="button" class="btn btn-light btn-lg px-5 js-start-button"><i class="fas fa-play me-2"></i>Start</button>
            </div>
        </div>
        <div class="qm-overlay js-off d-none">
            <div>
                <i class="fas fa-circle-pause fa-3x mb-3"></i>
                <h1 class="fw-bold">Queueing is currently off</h1>
                <p class="fs-5 mb-0">Please wait for the Registrar to open the queue.</p>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/queue-monitor.js') }}?v={{ @filemtime(public_path('js/queue-monitor.js')) }}"></script>
</body>
</html>
