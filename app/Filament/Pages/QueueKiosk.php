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

    public $selectedCounter = null;
    public $selectedInstansi = null;
    public $selectedService = null;

    // Daftar zona + deskripsi instansi
    public $counters = [
        1 => [
            'name' => 'Zona 1',
            'services' => [
                'Unit Pelayanan Pelayanan Terpadu Satu Pintu (UPTSP)',
            ],
        ],
        2 => [
            'name' => 'Zona 2',
            'services' => [
                'Kepolisian Resor Kota Besar',
                'Badan Narkotika Surabaya',
                'Bagian Pengadaan',
                'Bagian Pengadaan Barang/Jasa & Administrasi Pembangunan Kota Surabaya',
                'PT Pos Indonesia',
                'Badan Pendapatan Daerah',
            ],
        ],
        3 => [
            'name' => 'Zona 3',
            'services' => [
                'Dinas Kependudukan dan Pencatatan Sipil',
                'Pengadilan Negeri Surabaya',
                'Pengadilan Tata Usaha Negeri Surabaya',
                'Dinas Lingkungan Hidup',
                'Dinas Perumahan Rakyat Kawasan Permukiman serta Tanaman (DPRKPP)',
            ],
        ],
        4 => [
            'name' => 'Zona 4',
            'services' => [
                'BPJS Kesehatan',
                'BPJS Ketenagakerjaan',
                'Bursa Tenaga Kerja',
                'Perumda Air Minum Surya Sembada',
                'Direktorat Jenderal Pajak',
                'Pengadilan Agama',
                'Kantor Pertanahan Kota Surabaya I',
                'Kantor Pertanahan Kota Surabaya II',
            ],
        ],
        5 => [
            'name' => 'Zona 5',
            'services' => [
                'Kejaksaan Negeri Tanjung Perak',
                'Kejaksaan Negeri Kota Surabaya',
                'Klinik Investasi'
            ],
        ],
    ];

        // Data untuk ditampilkan
    public $instansis; // Collection of Instansi
    public $services;  // Collection of Service

    public function mount(): void
    {
        $this->instansis = collect();
        $this->services = collect();
    }

    public function getCountersProperty()
    {
        return Counter::with('service')->get();
    }

    protected function getViewData(): array
    {
        return [
            // ambil semua counter beserta instansinya & layanan dalam instansi tsb
            'counters' => Counter::with('instansis.services')->get(),
        ];
    }

    public function selectCounter($counterId)
    {
        $this->selectedCounter = (int) $counterId;
        $this->selectedInstansi = null;
        $this->selectedService = null;

        // Ambil instansi yang terkait dengan counter ini
        $this->instansis = Instansi::where('counter_id', $this->selectedCounter)
                            ->orderBy('nama_instansi')
                            ->get();

        // kosongkan services
        $this->services = collect();
    }

    public function selectInstansi($instansiId)
    {
        $this->selectedInstansi = (int) $instansiId;
        $this->selectedService = null;

        // Ambil layanan yang terkait dengan instansi ini
        $this->services = Service::where('instansi_id', $this->$instansiId)
                            ->orderBy('name') // atau 'nama_service' jika berbeda
                            ->get();
    }

    public function selectService($serviceId)
    {
        $this->selectedService = Service::find($serviceId);
    }

    public function resetInstansi()
    {
        $this->selectedInstansi= null;
        $this->selectedService = null;
        $this->services = collect();
    }

    public function resetSelection()
    {
        $this->selectedCounter = null;
        $this->selectedInstansi = null;
        $this->selectedService = null;
        $this->instansis = collect();
        $this->services = collect();
    }

    public function printStruk($serviceId)
    {
        // TODO: isi logic cetak struk
        $this->dispatchBrowserEvent('print-start', ['text' => "Cetak Struk untuk Service ID: {$serviceId}"]);
    }

    public function printBarcode($serviceId)
    {
        // TODO: isi logic cetak barcode
        $this->dispatchBrowserEvent('print-start', ['text' => "Cetak Barcode untuk Service ID: {$serviceId}"]);
    }

    // Method untuk "cetak antrian"
    public function printTicket(Service $service)
    {
        // logika cetak antrian (misal simpan ke tabel antrian)
        // Queue::create([...]);

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => "Tiket untuk layanan {$service->name} berhasil dicetak!"
        ]);
    }
}