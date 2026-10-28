<?php

namespace App\Http\Controllers\Employee\Settings;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\SettingDB\AdmissionMode;
use App\Models\SettingDB\EnrollmentMode;
use App\Models\SettingDB\QueueMode;
use App\Models\EvaluationDB\QCEsetting;

/**
 * Settings > Control Status: On / Off switches for system modules.
 * Every switch is described once in CONTROLS; the page renders a card per entry and
 * public/js/employee/settings/control-status.js saves a change with update().
 */
class SettingControlStatusController extends Controller
{
    private const ON = 'On';
    private const OFF = 'Off';

    // key => model + column holding 'On'/'Off'; per_campus rows are keyed by the user's campus
    private const CONTROLS = [
        'admission' => [
            'model' => AdmissionMode::class,
            'column' => 'statusadmission',
            'per_campus' => false,
            'title' => 'Admission Status Control',
            'label' => 'Admission Status',
            'icon' => 'ti ti-calendar-user',
            'note' => 'Check the checkbox if you want to <strong>"On"</strong> the Admission Portal.',
        ],
        'enrollment' => [
            'model' => EnrollmentMode::class,
            'column' => 'statusenroll',
            'per_campus' => true,
            'title' => 'Enrollment Status Control',
            'label' => 'Enrollment Status',
            'icon' => 'ti ti-device-laptop',
            'note' => 'Check the checkbox if you want to <strong>"Start / Open"</strong> the Enrollment</strong>.',
        ],
        'queue' => [
            'model' => QueueMode::class,
            'column' => 'statusqueue',
            'per_campus' => false,
            'title' => 'Queueing Status Control',
            'label' => 'Queueing Mode',
            'icon' => 'ti ti-line',
            'note' => 'Check the checkbox if you want to <strong>"On"</strong> the queueing.',
        ],
        'faculty-evaluation' => [
            'model' => QCEsetting::class,
            'column' => 'statuseval',
            'per_campus' => false,
            'title' => 'Faculty Evaluation Status Control',
            'label' => 'Faculty Evaluation Status',
            'icon' => 'ti ti-file-analytics',
            'note' => 'Check the checkbox if you want to <strong>"On"</strong> the Faculty Evaluation.',
        ],
    ];

    public static function keys(): array
    {
        return array_keys(self::CONTROLS);
    }

    public function index()
    {
        $controls = collect(self::CONTROLS)->map(fn ($control, $key) => $control + [
            'key' => $key,
            'is_on' => $this->currentValue($control) === self::ON,
        ]);

        return view('pages.employee.setting.control.aeqf', ['controls' => $controls]);
    }

    public function update(Request $request, string $control)
    {
        $config = self::CONTROLS[$control];

        $validated = $request->validate([
            'status' => ['required', 'boolean'],
        ]);

        $value = $validated['status'] ? self::ON : self::OFF;

        $record = $this->record($config);
        $record->{$config['column']} = $value;
        $record->save();

        return response()->json([
            'success' => true,
            'is_on' => $value === self::ON,
            'message' => $config['label'] . ($value === self::ON ? ' is now On (Start / Open).' : ' is now Off (Stop / Close).'),
        ]);
    }

    private function currentValue(array $config): ?string
    {
        return $this->query($config)->value($config['column']);
    }

    // The row a switch reads/writes; created as 'Off' when it doesn't exist yet
    private function record(array $config)
    {
        $match = $config['per_campus'] ? ['campus' => Auth::guard('web')->user()->campus] : [];

        return $config['model']::firstOrCreate($match, [$config['column'] => self::OFF]);
    }

    private function query(array $config)
    {
        $query = $config['model']::query();

        return $config['per_campus'] ? $query->where('campus', Auth::guard('web')->user()->campus) : $query;
    }
}
