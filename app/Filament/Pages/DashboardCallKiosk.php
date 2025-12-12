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
    
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return \Illuminate\Support\Facades\Auth::check();
    }


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
        
        Log::info('DashboardCallKiosk mount started', [
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'user_counter_id' => $user?->counter_id
        ]);

        // Jika operator, batasi hanya ke loket yang ditugaskan
        if ($user && $user->role === 'operator' && $user->counter_id) {
            // Gunakan withoutGlobalScopes untuk memastikan counter ditemukan
            $counter = Counter::withoutGlobalScopes()
                ->with(['service', 'instansi', 'assignedServices'])
                ->find($user->counter_id);
            
            if ($counter) {
                $this->counters = collect([$counter]);
                $this->selectedCounterId = $counter->id; // Pastikan menggunakan counter->id, bukan user->counter_id
                
                // Cari service yang memiliki counter_id yang sama
                $servicesByCounterId = Service::where('counter_id', $counter->id)->pluck('id', 'name')->toArray();
                
                // Log untuk debugging
                Log::info('Operator counter loaded successfully', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'counter_id' => $counter->id,
                    'counter_name' => $counter->name,
                    'service_id' => $counter->service_id,
                    'service_name' => $counter->service?->name,
                    'instansi_id' => $counter->instansi_id,
                    'instansi_name' => $counter->instansi?->nama_instansi,
                    'assigned_services_count' => $counter->assignedServices->count(),
                    'assigned_services' => $counter->assignedServices->pluck('id', 'name')->toArray(),
                    'services_by_counter_id' => $servicesByCounterId,
                    'selected_counter_id' => $this->selectedCounterId,
                    'is_active' => $counter->is_active,
                    'counter_loaded' => true,
                    'counters_collection_count' => $this->counters->count()
                ]);
            } else {
                $this->counters = collect();
                $this->selectedCounterId = null;
                
                Log::error('Counter not found for operator', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_counter_id' => $user->counter_id,
                    'all_counters' => Counter::withoutGlobalScopes()->pluck('id', 'name')->toArray()
                ]);
            }
        } else {
            // Admin bisa melihat semua loket yang ada (sama seperti di manajemen loket)
            $this->counters = Counter::with(['service', 'instansi', 'assignedServices'])
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
        // Jika selectedCounterId null, coba ambil dari user untuk operator
        if (!$this->selectedCounterId) {
            $user = Auth::user();
            if ($user && $user->role === 'operator' && $user->counter_id) {
                $this->selectedCounterId = $user->counter_id;
                Log::info('getSelectedCounterProperty: Set selectedCounterId from user', [
                    'user_id' => $user->id,
                    'selected_counter_id' => $this->selectedCounterId
                ]);
            } else {
                Log::warning('getSelectedCounterProperty: selectedCounterId is null', [
                    'user_id' => Auth::id(),
                    'user_role' => Auth::user()?->role,
                    'user_counter_id' => Auth::user()?->counter_id
                ]);
                return null;
            }
        }
        
        // Pastikan counter dimuat dengan relasi service dan instansi
        // Gunakan withoutGlobalScopes untuk operator agar counter selalu ditemukan
        $user = Auth::user();
        $query = Counter::with(['service', 'instansi', 'assignedServices']);
        
        if ($user && $user->role === 'operator') {
            $query = $query->withoutGlobalScopes();
        }
        
        $counter = $query->find($this->selectedCounterId);
        
        if (!$counter) {
            Log::error('getSelectedCounterProperty: Counter not found', [
                'selected_counter_id' => $this->selectedCounterId,
                'user_id' => $user?->id,
                'user_role' => $user?->role,
                'user_counter_id' => $user?->counter_id,
                'all_counters' => Counter::withoutGlobalScopes()->pluck('id', 'name')->toArray()
            ]);
        }
        
        return $counter;
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
        
        // Log untuk debugging
        $user = Auth::user();
        $query = Counter::with(['service', 'instansi', 'assignedServices']);
        
        if ($user && $user->role === 'operator') {
            $query = $query->withoutGlobalScopes();
        }
        
        $counter = $query->find($counterId);
        Log::info('Counter selected', [
            'counter_id' => $counterId,
            'counter_name' => $counter?->name,
            'service_id' => $counter?->service_id,
            'service_name' => $counter?->service?->name,
            'instansi_id' => $counter?->instansi_id,
        ]);
        
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
        
        // Cari antrian berikutnya berdasarkan service_id dari counter
        // Hanya ambil service yang benar-benar terkait dengan counter, bukan semua service di zona
        $nextQueue = null;
        
        // Kumpulkan semua service_id yang terkait dengan counter ini
        $serviceIds = [];
        
        // 1. Cek service_id langsung dari counter
        if ($this->selectedCounter->service_id) {
            $serviceIds[] = $this->selectedCounter->service_id;
        }
        
        // 2. Cek relasi many-to-many counter_service
        $assignedServices = $this->selectedCounter->assignedServices;
        if ($assignedServices->isNotEmpty()) {
            foreach ($assignedServices as $service) {
                if (!in_array($service->id, $serviceIds)) {
                    $serviceIds[] = $service->id;
                }
            }
        }
        
        // 3. Cek service dari relasi service() jika ada
        if ($this->selectedCounter->service && !in_array($this->selectedCounter->service->id, $serviceIds)) {
            $serviceIds[] = $this->selectedCounter->service->id;
        }
        
        // 4. Cari service yang memiliki counter_id yang sama dengan counter ini
        $servicesByCounterId = Service::where('counter_id', $this->selectedCounter->id)->pluck('id')->toArray();
        foreach ($servicesByCounterId as $serviceId) {
            if (!in_array($serviceId, $serviceIds)) {
                $serviceIds[] = $serviceId;
            }
        }
        
        // Hapus fallback berdasarkan prefix dan instansi_id yang terlalu luas
        // Ini menyebabkan semua antrian di zona yang sama muncul
        
        // Kumpulkan semua service yang ditemukan untuk logging
        $allServicesFound = [];
        if (!empty($serviceIds)) {
            $allServicesFound = Service::whereIn('id', $serviceIds)
                ->get(['id', 'name', 'prefix'])
                ->map(function($s) {
                    return ['id' => $s->id, 'name' => $s->name, 'prefix' => $s->prefix];
                })
                ->toArray();
        }
        
        Log::info('Service IDs for counter', [
            'counter_id' => $this->selectedCounter->id,
            'counter_name' => $this->selectedCounter->name,
            'service_ids' => $serviceIds,
            'services_found' => $allServicesFound,
            'direct_service_id' => $this->selectedCounter->service_id,
            'assigned_services_count' => $assignedServices->count(),
            'services_by_counter_id' => $servicesByCounterId,
            'prefix_patterns' => $prefixPatterns ?? [],
            'total_service_ids' => count($serviceIds)
        ]);
        
        // Jika tidak ada service_id sama sekali, tampilkan error
        if (empty($serviceIds)) {
            Log::warning('Counter does not have any service_id', [
                'counter_id' => $this->selectedCounter->id,
                'counter_name' => $this->selectedCounter->name,
                'counter_data' => $this->selectedCounter->toArray()
            ]);
            
            // Dispatch notification untuk user
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Loket ' . $this->selectedCounter->name . ' belum memiliki layanan yang ditetapkan. Silakan hubungi administrator.'
            ]);
            return;
        }
        
        // Cari antrian berdasarkan service_id yang terkait dengan counter
        $nextQueue = Queue::whereIn('service_id', $serviceIds)
            ->where('status', 'waiting')
            ->whereNull('called_at')
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->orderBy('created_at', 'asc')
            ->first();
        
        // Log untuk debugging
        if (!$nextQueue) {
            $waitingQueuesCount = Queue::whereIn('service_id', $serviceIds)
                ->where('status', 'waiting')
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->count();
            
            Log::info('No queue found by service_id', [
                'counter_id' => $this->selectedCounter->id,
                'counter_name' => $this->selectedCounter->name,
                'service_ids' => $serviceIds,
                'waiting_queues_count' => $waitingQueuesCount,
                'all_waiting_queues' => Queue::where('status', 'waiting')
                    ->whereDate('created_at', now()->format('Y-m-d'))
                    ->pluck('service_id', 'number')
                    ->toArray()
            ]);
        }
        
        // Jika tidak ada antrian dengan service_id, coba cari berdasarkan counter_id (fallback)
        if (!$nextQueue) {
            Log::info('No queue found by service_id, trying counter_id fallback');
            $nextQueue = Queue::where('counter_id', $this->selectedCounter->id)
                ->where('status', 'waiting')
                ->whereNull('called_at')
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->orderBy('created_at', 'asc')
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
            $nextQueue->load(['service', 'counter.instansi', 'counter.assignedServices']);
            
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

            // Kumpulkan semua service ID yang terkait dengan counter ini
            // Hanya ambil service yang benar-benar terkait dengan counter, bukan semua service di zona
            $serviceIds = [];
            
            // 1. Cek service_id langsung dari counter
            if ($counter->service_id) {
                $serviceIds[] = $counter->service_id;
            }
            
            // 2. Cek relasi many-to-many counter_service
            $assignedServices = $counter->assignedServices;
            if ($assignedServices->isNotEmpty()) {
                foreach ($assignedServices as $service) {
                    if (!in_array($service->id, $serviceIds)) {
                        $serviceIds[] = $service->id;
                    }
                }
            }
            
            // 3. Cek service dari relasi service() jika ada
            if ($counter->service && !in_array($counter->service->id, $serviceIds)) {
                $serviceIds[] = $counter->service->id;
            }
            
            // 4. Cari service yang memiliki counter_id yang sama dengan counter ini
            $servicesByCounterId = Service::where('counter_id', $counter->id)->pluck('id')->toArray();
            foreach ($servicesByCounterId as $serviceId) {
                if (!in_array($serviceId, $serviceIds)) {
                    $serviceIds[] = $serviceId;
                }
            }
            
            // Hapus fallback berdasarkan prefix dan instansi_id yang terlalu luas
            // Ini menyebabkan semua antrian di zona yang sama muncul
            
            // Jika tidak ada service_id, gunakan counter_id sebagai fallback
            if (empty($serviceIds)) {
                $waitingQueues = Queue::where('counter_id', $counter->id)
                    ->whereIn('status', ['waiting'])
                    ->whereNull('called_at')
                    ->whereDate('created_at', now()->format('Y-m-d'))
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $baseQuery = Queue::where('counter_id', $counter->id)
                    ->whereDate('created_at', now()->format('Y-m-d'));
            } else {
                $waitingQueues = Queue::whereIn('service_id', $serviceIds)
                    ->whereIn('status', ['waiting'])
                    ->whereNull('called_at')
                    ->whereDate('created_at', now()->format('Y-m-d'))
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $baseQuery = Queue::whereIn('service_id', $serviceIds)
                    ->whereDate('created_at', now()->format('Y-m-d'));
            }
            $stats['total'] = (clone $baseQuery)->count();
            $stats['finished'] = (clone $baseQuery)->where('status', 'finished')->count();
            $stats['waiting'] = $waitingQueues->count();
            $stats['cancelled'] = (clone $baseQuery)->where('status', 'canceled')->count();
        }

        // Kirim semua data yang dibutuhkan ke view
        return [
            'currentQueue' => $currentQueue,
            'waitingQueues' => $waitingQueues,
            'stats' => $stats,
        ];
    }
}