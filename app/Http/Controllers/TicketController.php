<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Service;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class TicketController extends Controller
{
    public function queuePdf(Queue $queue)
    {
        $service = Service::with('instansi')->find($queue->service_id);

        // Build QR as data URI so DomPDF can embed it easily
        $payload = [
            'mall' => 'MALL PELAYANAN PUBLIK KOTA SURABAYA',
            'zona' => request('zona', 'Zona'),
            'loket' => $service?->instansi?->nama_instansi ?? '-',
            'layanan' => $service?->name ?? '-',
            'nomor' => $queue->number,
            'tanggal' => now()->translatedFormat('j F Y'),
            'waktu' => now()->format('H:i:s'),
            // Payload untuk QR/Barcode
            'qrData' => json_encode([
                'queue_id' => $queue->id,
                'number' => $queue->number,
                'service' => $service?->name,
                'created_at' => $queue->created_at?->toIso8601String(),
            ]),
        ];

        $qr = QrCode::create($payload['qrData'])
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setSize(280)
            ->setMargin(0);
        $writer = new PngWriter();
        $qrDataUri = $writer->write($qr)->getDataUri();

        $data = $payload + ['qrDataUri' => $qrDataUri];

        $pdf = Pdf::loadView('pdf.ticket', $data)->setPaper('a6', 'portrait');
        return $pdf->stream('ticket-'.$queue->number.'.pdf');
    }
}


