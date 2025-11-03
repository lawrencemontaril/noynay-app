<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Exception;
use Dompdf\Options;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice)
    {
        $invoice->load(['appointment.patient', 'invoiceItems', 'payments']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'noynay_logo' => public_path('noynay_logo.png')
        ])->setPaper('A4', 'portrait');

        $fileName = 'Invoice-'.$invoice->id.'.pdf';

        return $pdf->download($fileName);

        // return $pdf->stream($fileName);
    }

    protected function imageToDataUrl(string $filename): string
    {
        if (! file_exists($filename))
            throw new Exception('File not found.');

        $mime = mime_content_type($filename);
        if ($mime === false)
            throw new Exception('Illegal MIME type.');

        $raw_data = file_get_contents($filename);
        if (empty($raw_data))
            throw new Exception('File not readable or empty.');

        return "data:{$mime};base64,".base64_encode($raw_data);
    }
}
