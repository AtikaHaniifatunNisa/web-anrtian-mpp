<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Service;
use App\Models\Queue;

class StrukController extends Controller
{
    public function generateStruk(Request $request)
    {
        $serviceId = $request->input('service_id');
        $queueId = $request->input('queue_id');
        
        // Ambil data service
        $service = Service::with('instansi')->find($serviceId);
        if (!$service) {
            return response()->json(['error' => 'Layanan tidak ditemukan'], 404);
        }

        // Jika ada queue_id, gunakan antrian yang sudah ada
        if ($queueId) {
            $queue = Queue::find($queueId);
            if (!$queue) {
                return response()->json(['error' => 'Antrian tidak ditemukan'], 404);
            }
            $queueNumber = $queue->number;
        } else {
            // Generate nomor antrian baru (untuk preview)
            $queueNumber = $this->generateQueueNumber($service);
        }
        
        // Siapkan data struk
        $strukData = [
            'mall' => 'MALL PELAYANAN PUBLIK',
            'kota' => 'KOTA SURABAYA',
            'zona' => $request->input('zona', 'Zona 1'),
            'loket' => $service->instansi?->nama_instansi ?? 'Loket',
            'layanan' => $service->name,
            'nomor' => $queueNumber,
            'tanggal' => now()->translatedFormat('j F Y'),
            'waktu' => now()->format('H:i:s'),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdf.struk-antrian', ['data' => $strukData])
            ->setPaper([0, 0, 226.77, 226.77], 'portrait') // 80mm x 80mm dalam points (persegi)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Courier New'
            ]);

        return $pdf->stream('struk-antrian-' . $queueNumber . '.pdf');
    }

    public function previewStruk(Request $request)
    {
        $serviceId = $request->input('service_id');
        
        // Ambil data service
        $service = Service::with('instansi')->find($serviceId);
        if (!$service) {
            return response()->json(['error' => 'Layanan tidak ditemukan'], 404);
        }

        // Generate nomor antrian (preview, tidak disimpan)
        $queueNumber = $this->generateQueueNumber($service);
        
        // Siapkan data struk
        $strukData = [
            'mall' => 'MALL PELAYANAN PUBLIK',
            'kota' => 'KOTA SURABAYA',
            'zona' => $request->input('zona', 'Zona 1'),
            'loket' => $service->instansi?->nama_instansi ?? 'Loket',
            'layanan' => $service->name,
            'nomor' => $queueNumber,
            'tanggal' => now()->translatedFormat('j F Y'),
            'waktu' => now()->format('H:i:s'),
        ];

        // Generate PDF untuk preview
        $pdf = Pdf::loadView('pdf.struk-antrian', ['data' => $strukData])
            ->setPaper([0, 0, 226.77, 226.77], 'portrait') // 80mm x 80mm dalam points (persegi)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Courier New'
            ]);

        return $pdf->stream('preview-struk-antrian.pdf');
    }

    private function generateQueueNumber($service)
    {
        // Generate nomor antrian berdasarkan prefix dan padding
        $prefix = $service->prefix ?? 'A';
        $padding = $service->padding ?? 3;
        
        // Cari nomor terakhir untuk layanan ini hari ini
        $lastQueue = Queue::where('service_id', $service->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastQueue ? (intval(substr($lastQueue->number, strlen($prefix))) + 1) : 1;
        
        return $prefix . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
    }
}
