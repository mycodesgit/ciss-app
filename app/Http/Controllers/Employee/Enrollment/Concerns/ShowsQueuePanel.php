<?php

namespace App\Http\Controllers\Employee\Enrollment\Concerns;

use App\Models\SettingDB\QueueCounter;
use App\Models\SettingDB\QueueCustomer;
use App\Models\SettingDB\QueueMode;

/**
 * View data of the queue panel and List of Pre-Enrolled Students, shown on the Enroll Student and
 * Edit Enrollment search pages (partials/queue-panel, partials/pre-enrolled-list). Uses ChecksEnrollment.
 */
trait ShowsQueuePanel
{
    protected function queuePanelData(): array
    {
        $queueCounter = QueueCounter::where('useridlog', $this->user()->id)->first();

        return [
            'queueOn' => QueueMode::value('statusqueue') !== 'Off',
            'queueCounter' => $queueCounter,
            // Number the counter is serving now (shown in Current Serving)
            'servingNumber' => $queueCounter?->currentid ? QueueCustomer::whereKey($queueCounter->currentid)->value('queue_number') : null,
            'hasQueueCounter' => $this->user()->campus === 'MC', // only Main Campus has queue counters
        ];
    }
}
