<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use App\Models\Service;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapLayananExport;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class MonitoringDashboard extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Monitoring Dashboard';
    protected static ?string $navigationGroup = 'Laporan & Monitoring';
    protected static string $view             = 'filament.pages.monitoring-dashboard';
    
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->role === 'admin';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->role === 'admin';
    }

    // filter tanggal sederhana
    public ?string $from = null;
    public ?string $to   = null;

    public function mount(): void
    {
        $this->from = now()->toDateString();
        $this->to   = now()->toDateString();

        $this->form->fill([
            'from' => $this->from,
            'to'   => $this->to,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\DatePicker::make('from')
                    ->label('Dari Tanggal')
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->from = $state),

                Forms\Components\DatePicker::make('to')
                    ->label('Sampai Tanggal')
                    ->reactive()
                    ->afterStateUpdated(fn ($state) => $this->to = $state),

                Forms\Components\Placeholder::make('info')
                    ->content('Pilih tanggal untuk filter & export'),
            ]),
        ])->statePath('data'); // bebas, hanya untuk simpan state form
    }

    /**
     * Data yang dipakai di Blade (tabel rekap di halaman)
     */
    public function getViewData(): array
    {
        $from = now()->parse($this->from)->startOfDay();
        $to = now()->parse($this->to)->endOfDay();
        
        $rekapan = Service::query()
            ->withCount([
                'queues as queues_count' => function ($q) use ($from, $to) {
                    $q->whereBetween('created_at', [$from, $to]);
                },
                'queues as menunggu_count' => function ($q) use ($from, $to) {
                    $q->where('status', 'waiting')->whereBetween('created_at', [$from, $to]);
                },
                'queues as dipanggil_count' => function ($q) use ($from, $to) {
                    $q->where('status', 'called')->whereBetween('created_at', [$from, $to]);
                },
                'queues as dilayani_count' => function ($q) use ($from, $to) {
                    $q->where('status', 'serving')->whereBetween('created_at', [$from, $to]);
                },
                'queues as selesai_count' => function ($q) use ($from, $to) {
                    $q->whereIn('status', ['completed', 'finished'])->whereBetween('created_at', [$from, $to]);
                },
                'queues as batal_count' => function ($q) use ($from, $to) {
                    $q->where('status', 'canceled')->whereBetween('created_at', [$from, $to]);
                },
            ])
            ->orderBy('name')
            ->get();

        return [
            'rekapan' => $rekapan,
        ];
    }

    /**
     * Tombol-tombol di header page Filament
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                // arahkan ke route export sambil bawa query from & to dari form
                ->url(fn () => route('export.rekap-layanan', [
                    'from' => $this->from,
                    'to'   => $this->to,
                ]), shouldOpenInNewTab: false),
        ];
    }
    public function getMonitoringRealTime()
    {
        $today = now()->toDateString();
        
        return Service::withCount([
            // jumlah antrian menunggu per layanan
            'queues as menunggu_count' => function ($q) use ($today) {
                $q->where('status', 'waiting')
                  ->whereDate('created_at', $today);
            },
            // jumlah antrian dipanggil per layanan
            'queues as dipanggil_count' => function ($q) use ($today) {
                $q->where('status', 'called')
                  ->whereDate('created_at', $today);
            },
            // jumlah antrian dilayani (sekarang)
            'queues as dilayani_count' => function ($q) use ($today) {
                $q->where('status', 'serving')
                  ->whereDate('created_at', $today);
            },
            // jumlah antrian selesai (completed + finished)
            'queues as selesai_count' => function ($q) use ($today) {
                $q->whereIn('status', ['completed', 'finished'])
                  ->whereDate('created_at', $today);
            },
            // jumlah antrian batal/lewat
            'queues as batal_count' => function ($q) use ($today) {
                $q->where('status', 'canceled')
                  ->whereDate('created_at', $today);
            },
        ])->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    public function getRekapJumlahPemohon()
    {
        $from = now()->parse($this->from)->startOfDay();
        $to   = now()->parse($this->to)->endOfDay();

        return DB::table('instansis as i')
            ->select(
                'i.instansi_id', 
                'i.nama_instansi as name', 
                DB::raw('COUNT(q.id) as total_pemohon')
            )
            ->leftJoin('services as s', 's.instansi_id', '=', 'i.instansi_id')
            ->leftJoin('queues as q', function ($join) use ($from, $to) {
                $join->on('q.service_id', '=', 's.id')
                    ->whereBetween('q.created_at', [$from, $to]);
            })
            ->groupBy('i.instansi_id', 'i.nama_instansi')
            ->orderBy('i.nama_instansi')
            ->get();
    }


    public function exportExcel()
    {
        return Excel::download(
            new RekapLayananExport($this->from, $this->to),
            'rekap_layanan.xlsx'
        );
    }

    #[On('refreshMonitoring')]
    public function refreshMonitoring()
    {
        // Method ini akan dipanggil oleh JavaScript untuk refresh data
        // Livewire akan otomatis refresh komponen
    }

}