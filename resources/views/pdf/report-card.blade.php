<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - {{ $student->stud_id }}</title>
    @php
        // "JUAN A. DELA CRUZ JR."
        $middle = $student->mname ? mb_substr($student->mname, 0, 1) . '.' : '';
        $ext = $student->ext && $student->ext !== 'N/A' ? $student->ext : '';
        $studentName = strtoupper(trim(preg_replace('/\s+/', ' ', "{$student->fname} {$middle} {$student->lname} {$ext}")));

        $preparer = strtoupper(trim(preg_replace('/\s+/', ' ', $preparedBy->fname . ' ' . ($preparedBy->mname ? mb_substr($preparedBy->mname, 0, 1) . '.' : '') . ' ' . $preparedBy->lname)));
        $semesterName = strtoupper(\App\Models\SettingDB\ConfigureCurrent::SEMESTERS[(int) $term->semester] ?? '');
    @endphp
    <style>
        /* Paper size is set by the controller (Legal portrait) */
        @page {
            margin: 12mm 16mm 14mm 16mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
        }

        .header {
            text-align: center;
        }

        .header img { width: 80%; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 2px 3px;
            text-align: left;
            vertical-align: top;
        }

        .info { margin-top: 18px; }
        .info td { padding: 2px 0; }
        .label { font-weight: bold; white-space: nowrap; width: 1%; padding-right: 10px !important; }

        .certify { margin-top: 22px; }
        .right { text-align: right; }

        .rule { border-top: 1px solid #000; }

        .semester {
            text-align: center;
            padding: 6px 0;
            font-weight: bold;
            letter-spacing: 0.04em;
        }

        table.subjects th {
            font-size: 9.5pt;
            padding: 6px 3px;
            border-bottom: 1px solid #000;
        }

        table.subjects td { font-size: 9.5pt; }

        .grade-col { width: 11%; text-align: center !important; }

        .nothing-follows {
            text-align: center;
            margin-top: 10px;
            letter-spacing: 0.05em;
        }

        .average {
            margin-top: 6px;
            font-weight: bold;
        }

        .remarks {
            margin-top: 50px;
            padding-top: 4px;
            font-size: 9pt;
        }

        .signatures { margin-top: 45px; }
        .signatures td { font-weight: bold; }

        .registrar {
            text-align: center;
            padding-top: 35px !important;
            font-size: 10.5pt;
        }

        .registrar .title {
            font-weight: normal;
            font-size: 9.5pt;
        }

        .prepared {
            margin-top: 50px;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    @if ($header)
        <div class="header">
            <img src="{{ public_path('uilibs/images/reportcard/' . $header) }}" alt="">
        </div>
    @endif

    <table class="info">
        <tr>
            <td class="label">STUDENT ID NO.:</td>
            <td><strong>{{ $student->stud_id }}</strong></td>
            <td class="label right">DATE:</td>
            <td class="right" style="width: 1%; white-space: nowrap;"><strong>{{ strtoupper(now()->format('F j, Y')) }}</strong></td>
        </tr>
        <tr>
            <td class="label">COURSE:</td>
            <td colspan="3"><strong>{{ $program }}</strong></td>
        </tr>
        <tr>
            <td class="label">YEAR &amp; SECTION:</td>
            <td colspan="3"><strong>{{ $enrollment->studYear }}-{{ $enrollment->studSec }}</strong></td>
        </tr>
    </table>

    <table class="certify">
        <tr>
            <td><strong>I hereby certify that: &nbsp;&nbsp; {{ $studentName }}</strong></td>
            <td class="right">has completed the following subjects in the</td>
        </tr>
        <tr>
            <td><strong>{{ $program }}</strong></td>
            <td class="right">curriculum and earned the</td>
        </tr>
        <tr>
            <td colspan="2">credits as indicated below during the School Year: &nbsp;&nbsp; <strong>{{ $term->schlyear }}</strong></td>
        </tr>
    </table>

    <div class="rule" style="margin-top: 16px;"></div>
    <div class="semester">{{ $semesterName }}</div>
    <div class="rule"></div>

    <table class="subjects" style="margin-top: 4px;">
        <thead>
            <tr>
                <th colspan="2" style="text-align: center;">SUBJECTS</th>
                <th class="grade-col">FINAL<br>GRADE</th>
                <th class="grade-col">COMPL<br>GRADE</th>
                <th class="grade-col">CREDITS</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td style="width: 18%;">{{ $row['subject'] }}</td>
                    <td>{{ $row['title'] }}</td>
                    <td class="grade-col">{{ $row['grade'] }}</td>
                    <td class="grade-col">{{ $row['completion'] }}</td>
                    {{-- NSTP: credits in parentheses (not part of the average) --}}
                    <td class="grade-col">{{ $row['nstp'] ? '(' . $row['credit'] . ')' : $row['credit'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 10px;">No subjects found for this term.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="nothing-follows">********************************** Nothing Follows **********************************</div>
    <div class="average">AVERAGE: {{ number_format($average, 6) }}</div>

    <div class="rule remarks">
        Remarks: This is a system generated report. Valid for evaluation purposes only.
    </div>

    <table class="signatures">
        <tr>
            <td style="width: 50%;">NOT VALID WITHOUT<br>SCHOOL SEAL</td>
            <td>CERTIFIED CORRECT:</td>
        </tr>
        <tr>
            <td></td>
            <td class="registrar">
                {{-- Campuses without a registrar in config ciss.report_card.registrars get an empty line --}}
                {{ $registrar['name'] ?? '______________________________' }}<br>
                <span class="title">{{ $registrar['title'] ?? 'Campus Registrar' }}</span>
            </td>
        </tr>
    </table>

    <div class="prepared">Prepared By: {{ $preparer }}</div>
</body>
</html>
