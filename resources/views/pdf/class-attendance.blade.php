<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Attendance - {{ $subjectLabel }} {{ $subject->subSec }}</title>
    <style>
        /* Paper size is set by the controller (Legal portrait). The bottom margin leaves room for the footer image */
        @page {
            margin: 10mm 12mm 22mm 12mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
        }

        /* Footer image on every page (fixed = drawn in the page margin) */
        .page-footer {
            position: fixed;
            bottom: -17mm;
            left: 0;
            right: 0;
            text-align: center;
        }

        .page-footer img { width: 70%; }

        .page-header {
            text-align: center;
        }

        .page-header img { width: 70%; }

        /* Subject / Course / Date, Time Schedule / Faculty in-charge */
        table.info {
            width: 100%;
            margin: 10px 0 8px;
            font-size: 11pt;
        }

        table.info td {
            padding: 3px 4px 3px 0;
            white-space: nowrap;
        }

        .info-label { width: 1%; }
        .info-data { font-weight: bold; text-decoration: underline; }

        /* 25 rows x 2 sides: No. | Name | Signature */
        table.sheet {
            width: 100%;
            border-collapse: collapse;
        }

        table.sheet th,
        table.sheet td {
            border: 1px solid #000;
            padding: 0 5px;
            height: 24px;
            vertical-align: middle;
        }

        table.sheet th {
            text-align: center;
            font-size: 9pt;
            padding: 4px;
        }

        table.sheet td.no {
            width: 5%;
            text-align: center;
        }

        table.sheet td.name {
            width: 28%;
            font-size: 9pt;
        }

        table.sheet .divider { border-left: 2px solid #000; }

        .remarks {
            margin-top: 12px;
            font-size: 11pt;
            line-height: 2;
        }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @php
        $rowsPerSide = 25;
        $perPage = $rowsPerSide * 2; // left side, then right side
        $pages = $students->chunk($perPage)->whenEmpty(fn ($pages) => $pages->push(collect())); // an empty sheet when no students
    @endphp

    <!-- Fixed elements go first so dompdf repeats them on every page -->
    <div class="page-footer">
        <img src="{{ public_path('uilibs/images/attendance/attendancefooter.png') }}" alt="">
    </div>

    @foreach ($pages as $page)
        @php
            $page = $page->values();
            $first = ($loop->index * $perPage) + 1; // number of the page's first student
        @endphp

        <div class="page-header">
            <img src="{{ public_path('uilibs/images/attendance/attendanceheader.png') }}" alt="">
        </div>

        <table class="info">
            <tr>
                <td class="info-label">Subject:</td>
                <td class="info-data">{{ $subjectLabel }} - {{ Str::limit($subject->sub_title, 35) }}</td>
                <td class="info-label">Course:</td>
                <td class="info-data">{{ $subject->subSec }}</td>
                <td class="info-label">Date:</td>
                <td>______________</td>
            </tr>
            <tr>
                <td class="info-label">Time Schedule:</td>
                <td>______________________</td>
                <td class="info-label">Faculty in-charge:</td>
                <td colspan="3">______________________________</td>
            </tr>
            <tr>
                <td class="info-label">Term:</td>
                <td colspan="5">{{ $term->label }}</td>
            </tr>
        </table>

        <table class="sheet">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name<br><i>(Last, First, Middle Initial)</i></th>
                    <th>Signature</th>
                    <th class="divider">No.</th>
                    <th>Name<br><i>(Last, First, Middle Initial)</i></th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @for ($row = 0; $row < $rowsPerSide; $row++)
                    @php
                        $left = $page->get($row);
                        $right = $page->get($row + $rowsPerSide);
                    @endphp
                    <tr>
                        <td class="no">{{ $left ? $first + $row : '' }}</td>
                        {{-- No student record: the Student ID instead of the name --}}
                        <td class="name">{{ $left ? ($left->full_name ?: $left->stud_id) : '' }}</td>
                        <td></td>
                        <td class="no divider">{{ $right ? $first + $row + $rowsPerSide : '' }}</td>
                        <td class="name">{{ $right ? ($right->full_name ?: $right->stud_id) : '' }}</td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        @if ($loop->last)
            <div class="remarks">
                Remarks: ______________________________________________________________________________<br>
                ______________________________________________________________________________________<br>
                ______________________________________________________________________________________
            </div>
        @else
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
