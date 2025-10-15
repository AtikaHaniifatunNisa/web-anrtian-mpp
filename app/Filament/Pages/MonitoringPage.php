<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Queue;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapLayananExport;

class MonitoringPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.monitoring-dashboard';
    protected static ?string $title = 'Monitoring Antrian';

    public $tanggal;

    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d'); // default hari ini
    }

    public function getMonitoringRealTime()
    {
        $today = now()->toDateString();
        
        try {
            return [
                'menunggu' => Queue::where('status', 'waiting')
                    ->whereDate('created_at', $today)
                    ->with('service')
                    ->get(),
                'dilayani' => Queue::where('status', 'serving')
                    ->whereDate('created_at', $today)
                    ->with('service')
                    ->get(),
                'selesai' => Queue::where('status', 'completed')
                    ->whereDate('created_at', $today)
                    ->with('service')
                    ->get(),
                'skip' => Queue::where('status', 'skipped')
                    ->whereDate('created_at', $today)
                    ->with('service')
                    ->get(),
            ];
        } catch (\Exception $e) {
            Log::error('Error in getMonitoringRealTime: ' . $e->getMessage());
            return [
                'menunggu' => collect(),
                'dilayani' => collect(),
                'selesai' => collect(),
                'skip' => collect(),
            ];
        }
    }

    public function getRekapHarian()
    {
        return Queue::join('services', 'queues.service_id', '=', 'services.id')
            ->select('services.name as layanan', DB::raw('count(*) as total'))
            ->whereDate('queues.created_at', $this->tanggal)
            ->groupBy('services.name')
            ->get();
    }
    public function exportExcel()
    {
        return Excel::download(new RekapLayananExport($this->tanggal, $this->tanggal), 'rekap-'.$this->tanggal.'.xlsx');
    }
        public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}