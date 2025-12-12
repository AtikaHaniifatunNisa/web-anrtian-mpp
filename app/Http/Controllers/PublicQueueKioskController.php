<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Service;
use App\Models\Instansi;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicQueueKioskController extends Controller
{
    // Data counter yang sama seperti di QueueKiosk Page
    private $counters = [
        1 => ['name' => 'Zona 1','services' => ['Unit Pelayanan Pelayanan Terpadu Satu Pintu (UPTSP)']],
        2 => ['name' => 'Zona 2','services' => ['Kepolisian Resor Kota Besar','Badan Narkotika Surabaya','Bagian Pengadaan','Bagian Pengadaan Barang/Jasa & Administrasi Pembangunan Kota Surabaya','PT Pos Indonesia','Badan Pendapatan Daerah']],
        3 => ['name' => 'Zona 3','services' => ['Dinas Kependudukan dan Pencatatan Sipil','Pengadilan Negeri Surabaya','Pengadilan Tata Usaha Negeri Surabaya','Dinas Lingkungan Hidup','Dinas Perumahan Rakyat Kawasan Permukiman serta Tanaman (DPRKPP)']],
        4 => ['name' => 'Zona 4','services' => ['BPJS Kesehatan','BPJS Ketenagakerjaan','Bursa Tenaga Kerja','Perumda Air Minum Surya Sembada','Direktorat Jenderal Pajak','Pengadilan Agama','Kantor Pertanahan Kota Surabaya I','Kantor Pertanahan Kota Surabaya II']],
        5 => ['name' => 'Zona 5','services' => ['Kejaksaan Negeri Tanjung Perak','Kejaksaan Negeri Surabaya','Klinik Investasi']],
    ];

    public function index(Request $request)
    {
        $selectedCounter = $request->get('zona');
        $selectedInstansi = $request->get('instansi');
        $selectedService = $request->get('service');

        $instansis = collect();
        $services = collect();

        // Jika zona dipilih, ambil instansi
        if ($selectedCounter && isset($this->counters[$selectedCounter])) {
            $counterName = $this->counters[$selectedCounter]['name'];
            $counterDb = Counter::whereRaw('UPPER(name) = UPPER(?)', [$counterName])->min('id');
            
            if ($counterDb) {
                $instansis = Instansi::where('counter_id', $counterDb)
                    ->orderBy('nama_instansi')
                    ->get();
                
                // Auto-select instansi jika hanya ada satu dan belum dipilih
                if ($instansis->count() === 1 && !$selectedInstansi) {
                    $selectedInstansi = $instansis->first()->instansi_id;
                }
            }
        }

        // Jika instansi dipilih, ambil services
        if ($selectedInstansi) {
            $services = Service::where('instansi_id', $selectedInstansi)
                ->orderBy('name')
                ->get();
        }

        return view('public.queue-kiosk', [
            'counters' => $this->counters,
            'selectedCounter' => $selectedCounter,
            'selectedInstansi' => $selectedInstansi,
            'selectedService' => $selectedService,
            'instansis' => $instansis,
            'services' => $services,
        ]);
    }

    public function selectInstansi(Request $request, $instansiId)
    {
        $selectedCounter = $request->get('zona');
        
        $services = Service::where('instansi_id', $instansiId)
            ->orderBy('name')
            ->get();

        return redirect()->route('public.queue-kiosk', [
            'zona' => $selectedCounter,
            'instansi' => $instansiId
        ]);
    }

    public function selectService(Request $request, $serviceId)
    {
        Log::info('selectService called with serviceId: ' . $serviceId);
        
        $service = Service::find($serviceId);
        if (!$service) {
            return redirect()->route('public.queue-kiosk')
                ->with('error', 'Layanan tidak ditemukan!');
        }

        Log::info('Selected service: ' . $service->name);

        // Generate nomor antrian
        $queueNumber = $this->generateQueueNumber($service);
        Log::info('Generated queue number: ' . $queueNumber);

        // Simpan data antrian ke database
        $queue = Queue::create([
            'number' => $queueNumber,
            'service_id' => $service->id,
            'status' => 'waiting',
            'created_at' => now(),
        ]);
        Log::info('Queue created with ID: ' . $queue->id);

        // Tentukan zona
        $selectedCounter = $request->get('zona', 1);
        $zona = $this->counters[$selectedCounter]['name'] ?? 'Zona 1';

        // Redirect ke PDF generator
        $pdfUrl = route('struk.generate', [
            'service_id' => $serviceId,
            'queue_id' => $queue->id,
            'zona' => $zona
        ]);
        Log::info('PDF URL generated: ' . $pdfUrl);

        return redirect($pdfUrl);
    }

    private function generateQueueNumber($service)
    {
        $prefix = $service->prefix ?? 'A';
        $padding = $service->padding ?? 0;

        $lastQueue = Queue::where('service_id', $service->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastQueue ? (intval(substr($lastQueue->number, strlen($prefix) + 1))) + 1 : 1;

        if ($padding == 0) {
            return $prefix . '-' . $nextNumber;
        }

        return $prefix . '-' . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
    }
}

