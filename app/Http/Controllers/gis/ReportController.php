<?php

namespace App\Http\Controllers\gis;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function generatePdf(Incident $incident): Response
    {
        $incident->load(['reporter', 'engineer', 'media', 'timeline.performer', 'reviews.engineer']);

        $html = view('reports.incident-pdf', compact('incident'))->render();

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'laporan-'.$incident->incident_number.'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
