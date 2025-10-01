<?php

namespace App\Filament\Pages;

use App\Models\Counter;
use App\Models\Service;
use App\Models\Instansi;
use Filament\Pages\Page;

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
        5 => ['name' => 'Zona 5','services' => ['Kejaksaan Negeri Tanjung Perak','Kejaksaan Negeri Kota Surabaya','Klinik Investasi']],
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
        $this->selectedService = Service::find($serviceId);
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
        $this->dispatch('print-start', text: "Cetak Struk untuk Service ID: {$serviceId}");
    }

    public function printBarcode($serviceId)
    {
        $this->dispatch('print-start', text: "Cetak Barcode untuk Service ID: {$serviceId}");
    }

    public function printTicket(Service $service)
    {
        $this->dispatch('notify', type: 'success', message: "Tiket untuk layanan {$service->name} berhasil dicetak!");
    }
}
