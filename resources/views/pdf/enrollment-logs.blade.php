<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollment Logs - {{ $studentId }}</title>
    <style>
        /* Paper size is set by the controller (Legal portrait). Top / bottom margins leave room for the header and footer images */
        @page {
            margin: 34mm 15mm 32mm 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9.5pt;
            color: #000;
            margin: 0;
        }

        /* Header and footer images on every page (fixed = drawn in the page margins) */
        .page-header,
        .page-footer {
            position: fixed;
            left: 0;
            right: 0;
        }

        .page-header { top: -27mm; }
        .page-footer { bottom: -25mm; }

        .page-header img,
        .page-footer img {
            width: 100%;
            display: block;
        }

        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 12px;
        }

        table.info {
            width: 100%;
            margin-bottom: 6px;
        }

        table.info td { padding: 2px 4px 2px 0; }
        .info-label { font-weight: bold; width: 18%; white-space: nowrap; }

        /* One block per save */
        .save {
            margin-top: 12px;
            page-break-inside: avoid;
        }

        .save-title {
            font-size: 10.5pt;
            font-weight: bold;
            background-color: #d9ead3;
            border: 1px solid #000;
            border-bottom: 0;
            padding: 4px 5px;
        }

        .save-title .latest { color: #0a6b2d; }

        table.list {
            width: 100%;
            border-collapse: collapse;
        }

        table.list th,
        table.list td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
        }

        table.list th {
            font-size: 9pt;
            text-align: left;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #555; }
    </style>
</head>
<body>
    <!-- Fixed elements go first so dompdf repeats them on every page -->
    <div class="page-header">
        <img src="{{ public_path('uilibs/images/allformheader/header.png') }}" alt="">
    </div>
    <div class="page-footer">
        <img src="{{ public_path('uilibs/images/allformheader/footer.png') }}" alt="">
    </div>

    <div class="doc-title">Enrollment Logs</div>
    <div class="doc-subtitle">
        {{ $campusName }} &middot; <strong>{{ $term->label }}</strong>
    </div>

    <table class="info">
        <tr>
            <td class="info-label">Student ID No.:</td>
            <td>{{ $studentId }}</td>
        </tr>
        <tr>
            <td class="info-label">Name:</td>
            <td>{{ $name }}</td>
        </tr>
        <tr>
            <td class="info-label">Times saved:</td>
            <td>{{ $saves->count() }}</td>
        </tr>
    </table>

    {{-- Oldest first; the last one is the enrollment as it is now --}}
    @foreach ($saves as $save)
        <div class="save">
            <div class="save-title">
                Save #{{ $loop->iteration }} &middot; {{ $save->savedAt }}
                @if ($loop->last)
                    <span class="latest">(latest)</span>
                @endif
            </div>
            <table class="list">
                <tr>
                    <td style="width: 50%;"><strong>Program:</strong> {{ $save->program }} ({{ $save->course }})</td>
                    <td><strong>Scholarship:</strong> {{ $save->scholarship ?: 'None' }}</td>
                </tr>
            </table>
            <table class="list">
                <thead>
                    <tr>
                        <th style="width: 15%;">Subject Code</th>
                        <th style="width: 13%;">Subject</th>
                        <th>Description</th>
                        <th style="width: 12%;">Section</th>
                        <th class="text-center" style="width: 7%;">Credit</th>
                        <th class="text-right" style="width: 9%;">Lec Fee</th>
                        <th class="text-right" style="width: 9%;">Lab Fee</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($save->subjects as $subject)
                        <tr>
                            <td>{{ $subject->code }}</td>
                            <td>{{ $subject->name }}</td>
                            <td>{{ $subject->title }}</td>
                            <td>{{ $subject->section }}</td>
                            <td class="text-center">{{ $subject->units }}</td>
                            <td class="text-right">{{ $subject->lecFee !== null ? number_format((float) $subject->lecFee, 2) : '' }}</td>
                            <td class="text-right">{{ $subject->labFee !== null ? number_format((float) $subject->labFee, 2) : '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center muted">No subjects were saved.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"><strong>Posted by:</strong> {{ $save->postedBy }}</td>
                        <td class="text-center"><strong>{{ $save->subjects->sum(fn ($subject) => (float) $subject->units) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endforeach
</body>
</html>
