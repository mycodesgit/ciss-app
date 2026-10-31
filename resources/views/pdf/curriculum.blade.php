<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Curriculum - {{ $program->progAcronym }}</title>
    <style>
        /* Paper size is set by the controller (Legal portrait) */
        @page {
            margin: 12mm 14mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #000;
            margin: 0;
        }

        h2 {
            text-align: center;
            font-size: 12pt;
            margin: 0 0 2px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 10px;
        }

        .year {
            font-weight: bold;
            font-size: 10pt;
            margin: 12px 0 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        th {
            background-color: #cdd6df;
        }

        .units { width: 50px; text-align: center; }
        .prerequisite { width: 110px; }
        .total td { font-weight: bold; }
    </style>
</head>
<body>
    <h2>{{ $program->progName }}</h2>
    <div class="subtitle">{{ $program->progAcronym }} ({{ $program->progCod }})</div>

    @forelse ($years as $year => $terms)
        <div class="year">{{ strtoupper($yearLevels[$year] ?? "Year Level {$year}") }}</div>

        @foreach ($terms as $semester => $subjects)
            <table>
                <thead>
                    <tr>
                        <th colspan="4">{{ strtoupper($semesters[$semester] ?? "Semester {$semester}") }}</th>
                    </tr>
                    <tr>
                        <th>Course No.</th>
                        <th>Descriptive Title</th>
                        <th class="units">Units</th>
                        <th class="prerequisite">Pre-Requisite</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjects as $subject)
                        <tr>
                            <td>{{ $subject->sub_name }}</td>
                            <td>{{ $subject->sub_title }}</td>
                            <td class="units">{{ $subject->sub_unit }}</td>
                            <td class="prerequisite">{{ $subject->prerequisite_name ?? $subject->prerequisite }}</td>
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td colspan="2">TOTAL</td>
                        <td class="units">{{ $subjects->sum(fn ($subject) => (float) $subject->sub_unit) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @empty
        <p>No subjects in this curriculum yet.</p>
    @endforelse
</body>
</html>
