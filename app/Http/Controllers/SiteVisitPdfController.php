<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class SiteVisitPdfController extends Controller
{
    public function download(Report $report): Response
    {
        Gate::authorize('downloadPdf', $report);

        $report->load([
            'user', 'unit', 'category',
            'siteVisit.ta', 'siteVisit.photos',
            'latestEngineerVerification.engineer',
            'latestDirectorApproval.director',
        ]);

        $pdf = Pdf::loadView('pdf.site-visit-report', compact('report'))
            ->setPaper('a4', 'portrait');

        $name = 'Laporan-Lawatan-' . ($report->file_number ?: $report->report_number) . '.pdf';
        $name = preg_replace('/[\\\\\/]+/', '-', $name);

        return $pdf->download($name);
    }
}
