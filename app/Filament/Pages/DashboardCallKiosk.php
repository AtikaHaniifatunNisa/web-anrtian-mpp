<?php

namespace App\Filament\Pages;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;
use App\Services\QueueService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View; // Penting untuk method render
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardCallKiosk extends Page
{
    // --- Konfigurasi Halaman Filament ---
    // protected static ?string $navigationIcon = 'heroicon-o-speakerphone';
    protected static string $view = 'filament.pages.dashboard-call-kiosk';
    protected static ?string $title = 'Loket Panggilan Antrian';
    protected static ?string $navigationLabel = 'Loket Panggilan';
    protected static ?string $navigationIcon = 'heroicon-o-speaker-wave';


    // --- Properti untuk State Komponen ---
    public $counters; // Akan menampung semua loket untuk navigasi
    public ?int $selectedCounterId = null; // ID dari loket yang sedang dipilih


    /**
     * Method `mount` dijalankan sekali saat komponen pertama kali dimuat.
     * Kita gunakan untuk inisialisasi data awal.
     */
    public function mount(): void
    {
        $user = Auth::user();

        // Jika operator, batasi hanya ke loket yang ditugaskan
        if ($user && $user->role === 'operator' && $user->counter_id) {
            $this->counters = Counter::with(['service', 'instansi'])->where('id', $user->counter_id)->get();
            $this->selectedCounterId = $user->counter_id;
        } else {
            // Admin bisa melihat semua loket yang ada (sama seperti di manajemen loket)
            $this->counters = Counter::with(['service', 'instansi'])
                ->orderBy('name')
                ->get();
            if ($this->counters->isNotEmpty()) {
                $this->selectedCounterId = $this->counters->first()->id;
            }
        }
    }

    /**
     * Ini adalah Computed Property.
     * Cara elegan untuk mendapatkan model Counter yang sedang dipilih.
     * Bisa diakses di view dengan `$this->selectedCounter`.
     */
    public function getSelectedCounterProperty(): ?Counter
    {
        if (!$this->selectedCounterId) {
            return null;
        }
        return Counter::find($this->selectedCounterId);
    }

    // --- Aksi yang Dipanggil dari View ---

    /**
     * Method ini dipanggil saat pengguna mengklik loket lain di navigasi.
     * Ini adalah inti dari fitur "live selection".
     */
    public function selectCounter(int $counterId): void
    {
        $user = Auth::user();

        // Operator tidak boleh berpindah loket di luar tugasnya
        if ($user && $user->role === 'operator' && $user->counter_id) {
            $this->selectedCounterId = $user->counter_id;
            return;
        }

        $this->selectedCounterId = $counterId;
        // Livewire akan otomatis me-render ulang komponen dengan data baru
    }

    public function callNext(QueueService $queueService)
    {
        Log::info('callNext method called', [
            'selectedCounterId' => $this->selectedCounterId,
            'selectedCounter' => $this->selectedCounter?->toArray(),
            'is_available' => $this->selectedCounter?->is_available,
            'is_active' => $this->selectedCounter?->is_active
        ]);
        
        if (!$this->selectedCounter) {
            Log::info('Call next blocked - no selected counter');
            return;
        }
        
        if (!$this->selectedCounter->is_available) {
            Log::info('Call next blocked - counter not available', [
                'is_active' => $this->selectedCounter->is_active,
                'hasServingQueue' => $this->selectedCounter->queues()->where('status', 'serving')->exists()
            ]);
            return;
        }
        
        // Cari antrian berikutnya berdasarkan service_id atau counter_id
        $nextQueue = null;
        if ($this->selectedCounter->service_id) {
            $nextQueue = Queue::where('service_id', $this->selectedCounter->service_id)
                ->where('status', 'waiting')
                ->where('called_at', null)
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->orderBy('created_at')
                ->first();
        } else {
            $nextQueue = Queue::where('counter_id', $this->selectedCounter->id)
                ->where('status', 'waiting')
                ->where('called_at', null)
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->orderBy('created_at')
                ->first();
        }

        if ($nextQueue) {
            Log::info('Found next queue', [
                'queueId' => $nextQueue->id,
                'queueNumber' => $nextQueue->number,
                'serviceName' => $nextQueue->service?->name
            ]);
            
            // Ubah status ke 'called' (dipanggil) bukan langsung 'serving'
            $nextQueue->update([
                'status' => 'called',
                'counter_id' => $this->selectedCounter->id,
                'called_at' => now()
            ]);
            
            // Refresh queue dengan relationships yang lengkap
            $nextQueue->refresh();
            $nextQueue->load(['service', 'counter.instansi']);
            
            // Dispatch event untuk suara pemanggilan dan tampilan TV
            $serviceName = $nextQueue->service ? $nextQueue->service->name : 'Layanan';
            $zonaName = $this->selectedCounter->name; // Menggunakan counter.name sebagai zona
            $servicePrefix = $nextQueue->service ? $nextQueue->service->prefix : 'A';
            
            $announcementData = [
                'queueNumber' => $nextQueue->number,
                'serviceName' => $serviceName,
                'servicePrefix' => $servicePrefix,
                'counterName' => $this->selectedCounter->name,
                'zona' => $zonaName,
                'calledAt' => now()->format('H:i:s')
            ];
            
            // Log data lengkap untuk debugging
            Log::info('Announcement data prepared:', [
                'queueNumber' => $announcementData['queueNumber'],
                'serviceName' => $announcementData['serviceName'],
                'counterName' => $announcementData['counterName'],
                'zona' => $announcementData['zona'],
                'calledAt' => $announcementData['calledAt'],
                'queueId' => $nextQueue->id,
                'serviceId' => $nextQueue->service_id,
                'counterId' => $this->selectedCounter->id,
                'instansiId' => $this->selectedCounter->instansi_id
            ]);
            
            Log::info('Dispatching announce-queue event', $announcementData);
            Log::info('Individual parameters:', [
                'queueNumber' => $announcementData['queueNumber'],
                'serviceName' => $announcementData['serviceName'],
                'counterName' => $announcementData['counterName'],
                'zona' => $announcementData['zona'],
                'calledAt' => $announcementData['calledAt']
            ]);
            
            $this->dispatch('announce-queue', $announcementData);
        } else {
            Log::info('No next queue found');
        }
    }

    public function markAsServing(QueueService $queueService, Queue $queue)
    {
        $queueService->serveQueue($queue);
    }

    public function callAgain(Queue $queue)
    {
        Log::info('Call again method called', [
            'queueId' => $queue->id,
            'queueNumber' => $queue->number
        ]);
        
        // Update called_at time untuk pemanggilan ulang
        $queue->update([
            'called_at' => now()
        ]);
        
        // Dispatch event untuk suara pemanggilan ulang
        $serviceName = $queue->service ? $queue->service->name : 'Layanan';
        $zonaName = $this->selectedCounter->name; // Menggunakan counter.name sebagai zona
        $servicePrefix = $queue->service ? $queue->service->prefix : 'A';
        
        $announcementData = [
            'queueNumber' => $queue->number,
            'serviceName' => $serviceName,
            'servicePrefix' => $servicePrefix,
            'counterName' => $this->selectedCounter->name,
            'zona' => $zonaName,
            'calledAt' => now()->format('H:i:s')
        ];
        
        $this->dispatch('announce-queue', $announcementData);
    }

    public function startServing(Queue $queue)
    {
        Log::info('Start serving method called', [
            'queueId' => $queue->id,
            'queueNumber' => $queue->number
        ]);
        
        // Ubah status dari 'called' ke 'serving'
        $queue->update([
            'status' => 'serving'
        ]);
    }

    public function markAsFinished(QueueService $queueService, Queue $queue)
    {
        $queueService->finishQueue($queue);
    }

    public function markAsCancelled(QueueService $queueService, Queue $queue)
    {
        $queueService->cancelQueue($queue);
    }

    public function cancelCalled(Queue $queue)
    {
        Log::info('Cancel called method called', [
            'queueId' => $queue->id,
            'queueNumber' => $queue->number
        ]);
        
        // Ubah status dari 'called' ke 'canceled' (batal/lewat)
        $queue->update([
            'status' => 'canceled',
            'counter_id' => null,
            'called_at' => null
        ]);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Antrian ' . $queue->number . ' dibatalkan dan masuk ke statistik batal/lewat.'
        ]);
    }


    public function toggleCounterStatus()
    {
        if ($this->selectedCounter) {
            $this->selectedCounter->update([
                'is_active' => !$this->selectedCounter->is_active
            ]);
            // Refresh data loket di navigasi (sama seperti di manajemen loket)
            $this->counters = Counter::with(['service', 'instansi'])
                ->orderBy('name')
                ->get();
        }
    }


    public function getViewData(): array
    {
        $currentQueue = null;
        $waitingQueues = collect(); // Default ke koleksi kosong
        $stats = [
            'total' => 0, 'finished' => 0, 'waiting' => 0, 'cancelled' => 0
        ];

        // Hanya ambil data jika ada loket yang dipilih
        if ($this->selectedCounter) {
            $counter = $this->selectedCounter; // Ambil dari computed property

            // Cari antrian yang sedang dipanggil atau dilayani di loket ini
            // Prioritas: cari yang sedang serving, lalu yang called (dipanggil)
            $currentQueue = Queue::where('counter_id', $counter->id)
                ->whereIn('status', ['serving', 'called'])
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->orderByRaw("CASE WHEN status = 'serving' THEN 1 WHEN status = 'called' THEN 2 END")
                ->first();

            // Cari antrian yang menunggu untuk layanan di loket ini
            if ($counter->service_id) {
                $waitingQueues = Queue::where('service_id', $counter->service_id)
                    ->whereIn('status', ['waiting'])
                    ->where('called_at', null)
                    ->whereDate('created_at', now()->format('Y-m-d'))
                    ->orderBy('created_at')
                    ->get();

                // Kalkulasi statistik berdasarkan service_id
                $baseQuery = Queue::where('service_id', $counter->service_id)->whereDate('created_at', now()->format('Y-m-d'));
                $stats['total'] = (clone $baseQuery)->count();
                $stats['finished'] = (clone $baseQuery)->where('status', 'finished')->count();
                $stats['waiting'] = $waitingQueues->count();
                $stats['cancelled'] = (clone $baseQuery)->where('status', 'canceled')->count();
            } else {
                // Jika counter tidak memiliki service_id, cari berdasarkan counter_id
                $waitingQueues = Queue::where('counter_id', $counter->id)
                    ->whereIn('status', ['waiting'])
                    ->where('called_at', null)
                    ->whereDate('created_at', now()->format('Y-m-d'))
                    ->orderBy('created_at')
                    ->get();

                $baseQuery = Queue::where('counter_id', $counter->id)->whereDate('created_at', now()->format('Y-m-d'));
                $stats['total'] = (clone $baseQuery)->count();
                $stats['finished'] = (clone $baseQuery)->where('status', 'finished')->count();
                $stats['waiting'] = $waitingQueues->count();
                $stats['cancelled'] = (clone $baseQuery)->where('status', 'canceled')->count();
            }
        }

        // Kirim semua data yang dibutuhkan ke view
        return [
            'currentQueue' => $currentQueue,
            'waitingQueues' => $waitingQueues,
            'stats' => $stats,
        ];
    }
}