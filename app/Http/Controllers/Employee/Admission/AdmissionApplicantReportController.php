<?php

namespace App\Http\Controllers\Employee\Admission;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Employee\Admission\Concerns\ManagesApplicants;

use Illuminate\Http\Request;
use PDF;
use ZipArchive;

use App\Models\AdmissionDB\Applicant;

/**
 * Admission > Reports > Applicants: every applicant of a year / campus / strand that was not cancelled,
 * with export (Excel / PDF / Print) from the table, each applicant's Admission Slip (PDF in a modal's
 * iframe) and the slips of the whole search as ZIP files. (public/js/employee/admission/report-applicants.js)
 */
class AdmissionApplicantReportController extends Controller
{
    use ManagesApplicants;

    // Slips per ZIP file (keeps one download within the time limit)
    public const SLIPS_PER_ZIP = 100;

    public function applicants()
    {
        return view('pages.employee.admission.report.applicants', $this->searchOptions() + [
            'slipsPerZip' => self::SLIPS_PER_ZIP,
        ]);
    }

    // DataTables source, filtered by the search form
    public function applicantsList(Request $request)
    {
        // Nothing until a strand is picked (the page's first load), so one search never loads every strand
        if (! $request->filled('strand')) {
            return response()->json(['data' => []]);
        }

        $applicants = $this->searchApplicants($request, Applicant::ACTIVE_STATUSES)
            ->get()
            ->map(fn (Applicant $applicant) => $this->present($applicant));

        return response()->json(['data' => $applicants]);
    }

    // One applicant's Admission Slip, the src of the slip modal's iframe
    public function admissionSlip(Applicant $applicant)
    {
        // Same campuses the report can show
        abort_unless($this->user()->canViewAllCampuses() || $applicant->campus === $this->user()->campus, 403, 'You can only view applicants of your own campus.');
        abort_if((int) $applicant->p_status === Applicant::STATUS_CANCELLED, 404);

        return $this->slipPdf($applicant)->stream($this->slipFileName($applicant));
    }

    // Admission Slips of the searched applicants, one batch (offset) of SLIPS_PER_ZIP as a ZIP
    public function admissionSlipsZip(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $request->validate(['strand' => ['required', 'string']]);
        $offset = max((int) $request->query('offset', 0), 0);

        // Ordered by id so the batches never skip or repeat an applicant
        $applicants = $this->searchApplicants($request, Applicant::ACTIVE_STATUSES)
            ->reorder('id')
            ->offset($offset)
            ->limit(self::SLIPS_PER_ZIP)
            ->get();

        abort_if($applicants->isEmpty(), 404, 'No applicants found for this batch.');

        $zipName = sprintf('Admission_Slips_%d_to_%d.zip', $offset + 1, $offset + $applicants->count());
        $zipPath = tempnam(sys_get_temp_dir(), 'slips');

        $zip = new ZipArchive();
        abort_unless($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Could not create the ZIP file.');

        $usedNames = [];
        foreach ($applicants as $applicant) {
            // Same name twice (e.g. two "DELA CRUZ, JUAN") gets the applicant id added
            $fileName = $this->slipFileName($applicant);
            if (in_array($fileName, $usedNames, true)) {
                $fileName = str_replace('.pdf', " [ID-{$applicant->id}].pdf", $fileName);
            }
            $usedNames[] = $fileName;

            $zip->addFromString($fileName, $this->slipPdf($applicant)->output());
        }

        $zip->close();

        return response()->download($zipPath, $zipName, ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }

    private function slipPdf(Applicant $applicant)
    {
        return PDF::loadView('pdf.admission-slip', ['applicant' => $applicant])->setPaper('a4', 'portrait');
    }

    // "LASTNAME, FIRSTNAME.pdf" (only letters, numbers, spaces, - and _)
    private function slipFileName(Applicant $applicant): string
    {
        $clean = fn ($value) => preg_replace('/[^A-Za-z0-9_\- ]/', '', (string) $value);

        return strtoupper(trim($clean($applicant->lname) . ', ' . $clean($applicant->fname))) . '.pdf';
    }
}
