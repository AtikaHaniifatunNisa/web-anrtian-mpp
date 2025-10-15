<?php

namespace App\Filament\Pages;

use App\Models\Counter;
use App\Models\Service;
use App\Models\Instansi;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class QueueKiosk extends Page
{
    protected static string $view = 'filament.pages.queue-kiosk';
    protected static ?string $title = 'Cetak Antrian';
    protected static ?string $navigationLabel = 'Kiosk Cetak Antrian';
    protected static ?string $navigationGroup = 'Display Kiosk';
    protected static ?string $navigationIcon = 'heroicon-o-printer';

    public $selectedCounter = null;        // key dari array $counters (1..5)
    public $selectedCounterDbId = null;    // <-- ID counter sebenarnya di DB
    public $selectedInstansi = null;
    public $selectedService  = null;

    public $counters = [
        1 => ['name' => 'Zona 1','services' => ['Unit Pelayanan Pelayanan Terpadu Satu Pintu (UPTSP)']],
        2 => ['name' => 'Zona 2','services' => ['Kepolisian Resor Kota Besar','Badan Narkotika Surabaya','Bagian Pengadaan','Bagian Pengadaan Barang/Jasa & Administrasi Pembangunan Kota Surabaya','PT Pos Indonesia','Badan Pendapatan Daerah']],
        3 => ['name' => 'Zona 3','services' => ['Dinas Kependudukan dan Pencatatan Sipil','Pengadilan Negeri Surabaya','Pengadilan Tata Usaha Negeri Surabaya','Dinas Lingkungan Hidup','Dinas Perumahan Rakyat Kawasan Permukiman serta Tanaman (DPRKPP)']],
        4 => ['name' => 'Zona 4','services' => ['BPJS Kesehatan','BPJS Ketenagakerjaan','Bursa Tenaga Kerja','Perumda Air Minum Surya Sembada','Direktorat Jenderal Pajak','Pengadilan Agama','Kantor Pertanahan Kota Surabaya I','Kantor Pertanahan Kota Surabaya II']],
        5 => ['name' => 'Zona 5','services' => ['Kejaksaan Negeri Tanjung Perak','Kejaksaan Negeri Surabaya','Klinik Investasi']],
    ];

    public $instansis;
    public $services;

    public function mount(): void
    {
        $this->instansis = collect();
        $this->services  = collect();
    }

    protected function getViewData(): array
    {
        return [
            'countersDb' => Counter::with('instansis.services')->get(),
        ];
    }

    public function selectCounter($arrayKey)
    {
        $this->selectedCounter = (int) $arrayKey;
        $this->selectedInstansi = null;
        $this->selectedService  = null;
        $this->services = collect();
    
        $counterName = $this->counters[$this->selectedCounter]['name'] ?? null;
    
        // Ambil MIN(id) untuk nama zona tsb (ZONA 1/2/3/4/5) - case-insensitive
        $this->selectedCounterDbId = \App\Models\Counter::whereRaw('UPPER(name) = UPPER(?)', [$counterName])
            ->min('id');
    
        if (!$this->selectedCounterDbId) {
            $this->instansis = collect();
            $this->dispatch('notify', type: 'warning', message: "Counter '{$counterName}' tidak ditemukan.");
            return;
        }
    
        $this->instansis = \App\Models\Instansi::where('counter_id', $this->selectedCounterDbId)
            ->orderBy('nama_instansi')
            ->get();
            
        // Auto-select instansi jika hanya ada satu instansi di zona ini
        if ($this->instansis->count() === 1) {
            $singleInstansi = $this->instansis->first();
            $this->selectInstansi($singleInstansi->instansi_id);
        }
    }
    

    public function selectInstansi($instansiId)
    {
        $this->selectedInstansi = (int) $instansiId;
        $this->selectedService = null;

        $this->services = Service::where('instansi_id', $instansiId)
            ->orderBy('name') // ganti ke 'nama_service' jika kolommu itu
            ->get();
    }

    // MASIH DIPAKAI? Kalau ya, gunakan selectedCounterDbId agar akurat.
    public function selectInstansiByName($label)
    {
        if (!$this->selectedCounterDbId) return;

        $instansi = Instansi::where('counter_id', $this->selectedCounterDbId)
            ->whereRaw('LOWER(TRIM(nama_instansi)) = LOWER(TRIM(?))', [$label])
            ->first();

        if ($instansi) {
            $this->selectInstansi($instansi->instansi_id);
        } else {
            $this->selectedInstansi = null;
            $this->services = collect();
            $this->dispatch('notify', type: 'warning', message: "Instansi '{$label}' belum terdaftar di database.");
        }
    }

    public function selectService($serviceId)
    {
        Log::info('selectService called with serviceId: ' . $serviceId);
        $this->selectedService = Service::find($serviceId);
        Log::info('Selected service: ' . ($this->selectedService ? $this->selectedService->name : 'null'));
    }

    public function resetInstansi()
    {
        $this->selectedInstansi = null;
        $this->selectedService  = null;
        $this->services = collect();
    }

    public function resetSelection()
    {
        $this->selectedCounter = null;
        $this->selectedCounterDbId = null;   // <-- reset juga
        $this->selectedInstansi = null;
        $this->selectedService  = null;
        $this->instansis = collect();
        $this->services  = collect();
    }

    public function printStruk($serviceId)
    {
        try {
            // Debug: Log service ID
            Log::info('=== PRINT STRUK CALLED ===');
            Log::info('printStruk called with serviceId: ' . $serviceId);
            Log::info('Current selectedService: ' . ($this->selectedService ? $this->selectedService->name : 'null'));
            
            // Ambil data service
            $service = Service::with('instansi')->find($serviceId);
            if (!$service) {
                Log::error('Service not found for ID: ' . $serviceId);
                $this->dispatch('notify', type: 'error', message: 'Layanan tidak ditemukan!');
                return;
            }
            
            Log::info('Service found: ' . $service->name);

            // Generate nomor antrian
            $queueNumber = $this->generateQueueNumber($service);
            Log::info('Generated queue number: ' . $queueNumber);
            
            // Simpan data antrian ke database
            $queue = \App\Models\Queue::create([
                'number' => $queueNumber,
                'service_id' => $service->id,
                'status' => 'waiting',
                'created_at' => now(),
            ]);
            Log::info('Queue created with ID: ' . $queue->id);
            
            // Redirect ke PDF generator
            $zona = $this->counters[$this->selectedCounter]['name'] ?? 'Zona 1';
            $pdfUrl = route('struk.generate', [
                'service_id' => $serviceId,
                'queue_id' => $queue->id,
                'zona' => $zona
            ]);
            Log::info('PDF URL generated: ' . $pdfUrl);
            
            // Test: Redirect langsung ke PDF
            Log::info('Redirecting to PDF: ' . $pdfUrl);
            return redirect($pdfUrl);
            
        } catch (\Exception $e) {
            Log::error('Error in printStruk: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->dispatch('notify', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function generateQueueNumber($service)
    {
        // Generate nomor antrian berdasarkan prefix dan padding
        $prefix = $service->prefix ?? 'A';
        $padding = $service->padding ?? 3;
        
        // Cari nomor terakhir untuk layanan ini hari ini
        $lastQueue = \App\Models\Queue::where('service_id', $service->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastQueue ? (intval(substr($lastQueue->number, strlen($prefix))) + 1) : 1;
        
        return $prefix . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
    }

    public function printBarcode($serviceId)
    {
        // Ambil data service
        $service = Service::with('instansi')->find($serviceId);
        if (!$service) {
            $this->dispatch('notify', type: 'error', message: 'Layanan tidak ditemukan!');
            return;
        }

        // Generate nomor antrian
        $queueNumber = $this->generateQueueNumber($service);
        
        // Simpan data antrian ke database
        $queue = \App\Models\Queue::create([
            'number' => $queueNumber,
            'service_id' => $service->id,
            'status' => 'waiting',
            'created_at' => now(),
        ]);
        
        // Redirect ke halaman barcode
        $zona = $this->counters[$this->selectedCounter]['name'] ?? 'Zona 1';
        $barcodeUrl = route('barcode.show', [
            'service_id' => $serviceId,
            'queue_id' => $queue->id,
            'zona' => $zona
        ]);
        
        // Buka halaman barcode di tab baru
        $this->dispatch('open-barcode', url: $barcodeUrl);
        
        // Notifikasi sukses
        $this->dispatch('notify', type: 'success', message: "Barcode nomor {$queueNumber} berhasil dibuat!");
    }


    public function printTicket(Service $service)
    {
        $this->dispatch('notify', type: 'success', message: "Tiket untuk layanan {$service->name} berhasil dicetak!");
    }
}
