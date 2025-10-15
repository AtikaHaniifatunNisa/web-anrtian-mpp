<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Service;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function show(Request $request)
    {
        $serviceId = $request->query('service_id');
        $queueId = $request->query('queue_id');
        $zona = $request->query('zona', 'Zona 1');

        $service = Service::with('instansi')->find($serviceId);
        $queue = Queue::find($queueId);

        if (!$service || !$queue) {
            abort(404, 'Data tidak ditemukan.');
        }

        // Generate URL untuk scan barcode (akan redirect ke PDF)
        // Gunakan IP address yang bisa diakses dari HP
        $baseUrl = 'http://192.168.137.1:8000';
        $scanUrl = $baseUrl . route('barcode.scan', [
            'service_id' => $serviceId,
            'queue_id' => $queueId,
            'zona' => $zona
        ], false);

        // Log untuk debug
        \Log::info('Barcode URL generated: ' . $scanUrl);

        // Generate QR Code
        $qrCode = QrCode::size(300)
            ->format('svg')
            ->generate($scanUrl);

        $data = [
            'service' => $service,
            'queue' => $queue,
            'zona' => $zona,
            'qrCode' => $qrCode,
            'scanUrl' => $scanUrl,
            'queueNumber' => $queue->number,
            'serviceName' => $service->name,
            'instansiName' => $service->instansi->nama_instansi ?? 'Tidak ada Instansi',
            'tanggal' => now()->translatedFormat('j F Y'),
            'waktu' => now()->format('H:i:s'),
        ];

        return view('barcode.show', $data);
    }

    public function scan(Request $request)
    {
        $serviceId = $request->query('service_id');
        $queueId = $request->query('queue_id');
        $zona = $request->query('zona', 'Zona 1');

        \Log::info('Barcode scan accessed with params:', [
            'service_id' => $serviceId,
            'queue_id' => $queueId,
            'zona' => $zona,
            'user_agent' => $request->header('User-Agent'),
            'is_mobile' => $this->isMobile($request)
        ]);

        $service = Service::with('instansi')->find($serviceId);
        $queue = Queue::find($queueId);

        if (!$service || !$queue) {
            \Log::error('Service or Queue not found:', [
                'service_id' => $serviceId,
                'queue_id' => $queueId,
                'service' => $service,
                'queue' => $queue
            ]);
            abort(404, 'Data tidak ditemukan.');
        }

        // Generate PDF URL dengan IP address yang bisa diakses dari HP
        $baseUrl = 'http://192.168.137.1:8000';
        $pdfUrl = $baseUrl . route('struk.generate', [
            'service_id' => $serviceId,
            'zona' => $zona
        ], false);

        \Log::info('Redirecting to PDF URL: ' . $pdfUrl);

        // Untuk mobile, gunakan JavaScript redirect yang lebih reliable
        if ($this->isMobile($request)) {
            return response()->view('barcode.mobile-redirect', [
                'pdfUrl' => $pdfUrl,
                'service' => $service,
                'queue' => $queue,
                'zona' => $zona
            ]);
        }

        return redirect($pdfUrl);
    }
    
    private function isMobile($request)
    {
        $userAgent = $request->header('User-Agent', '');
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 
            'BlackBerry', 'Windows Phone', 'Opera Mini'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
