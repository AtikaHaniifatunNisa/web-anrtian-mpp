<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Counter;
use Carbon\Carbon;

class TvDisplayController extends Controller
{
    public function getQueueStatus()
    {
        $today = Carbon::today();
        
        // Get all services with their status
        $services = Service::where('is_active', true)
            ->with(['counters' => function($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function($service) use ($today) {
                $activeCounters = $service->counters->count();
                
                // Get next queue for this service
                $nextQueue = Queue::where('service_id', $service->id)
                    ->where('status', 'waiting')
                    ->where('called_at', null)
                    ->whereDate('created_at', $today)
                    ->orderBy('created_at')
                    ->first();
                
                // Get current serving queue
                $servingQueue = Queue::where('service_id', $service->id)
                    ->where('status', 'serving')
                    ->whereDate('created_at', $today)
                    ->with('counter')
                    ->first();
                
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'prefix' => $service->prefix,
                    'nextQueue' => $nextQueue ? $nextQueue->number : null,
                    'servingQueue' => $servingQueue ? [
                        'number' => $servingQueue->number,
                        'counter' => $servingQueue->counter ? $servingQueue->counter->name : null
                    ] : null,
                    'activeCounters' => $activeCounters,
                    'totalCounters' => $activeCounters,
                    'status' => $servingQueue ? 'serving' : ($nextQueue ? 'waiting' : 'available')
                ];
            });

        // Get current serving queues for display
        $servingQueues = Queue::where('status', 'serving')
            ->whereDate('created_at', $today)
            ->with(['service', 'counter'])
            ->orderBy('called_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($queue) {
                return [
                    'number' => $queue->number,
                    'service' => $queue->service ? $queue->service->name : 'Layanan',
                    'counter' => $queue->counter ? $queue->counter->name : 'Loket'
                ];
            });

        // Get available counters
        $availableCounters = Counter::where('is_active', true)
            ->whereDoesntHave('queues', function($query) use ($today) {
                $query->where('status', 'serving')
                    ->whereDate('created_at', $today);
            })
            ->with('service')
            ->limit(3)
            ->get()
            ->map(function($counter) {
                return [
                    'name' => $counter->name,
                    'service' => $counter->service ? $counter->service->name : 'Layanan'
                ];
            });

        return response()->json([
            'services' => $services,
            'servingQueues' => $servingQueues,
            'availableCounters' => $availableCounters,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function getLatestAnnouncement()
    {
        $today = Carbon::today();
        
        // Get the most recent announcement (serving queue that was just called)
        $latestAnnouncement = Queue::where('status', 'serving')
            ->whereDate('created_at', $today)
            ->whereNotNull('called_at')
            ->with(['service', 'counter.instansi'])
            ->orderBy('called_at', 'desc')
            ->first();

        if (!$latestAnnouncement) {
            return response()->json(null);
        }

        return response()->json([
            'queueNumber' => $latestAnnouncement->number,
            'serviceName' => $latestAnnouncement->service ? $latestAnnouncement->service->name : 'Layanan',
            'counterName' => $latestAnnouncement->counter ? $latestAnnouncement->counter->name : 'Loket',
            'zona' => $latestAnnouncement->counter && $latestAnnouncement->counter->instansi 
                ? $latestAnnouncement->counter->instansi->nama_instansi 
                : 'Zona',
            'calledAt' => $latestAnnouncement->called_at->format('H:i:s')
        ]);
    }

    // Get services data for specific zone
    public function getZoneServices($zoneId)
    {
        $today = Carbon::today();
        
        // Get zone counter
        $zoneCounter = Counter::where('id', $zoneId)->first();
        if (!$zoneCounter) {
            return response()->json(['error' => 'Zone not found'], 404);
        }
        
        // Get services for this zone
        $services = Service::whereHas('instansi', function($query) use ($zoneId) {
            $query->where('counter_id', $zoneId);
        })
        ->where('is_active', true)
        ->with(['instansi'])
        ->get()
        ->map(function($service) use ($today) {
            // Get next queue for this service
            $nextQueue = Queue::where('service_id', $service->id)
                ->where('status', 'waiting')
                ->where('called_at', null)
                ->whereDate('created_at', $today)
                ->orderBy('created_at')
                ->first();
            
            // Get current serving queue
            $servingQueue = Queue::where('service_id', $service->id)
                ->where('status', 'serving')
                ->whereDate('created_at', $today)
                ->with('counter')
                ->first();
            
            // Count active counters for this service
            $activeCounters = Counter::where('service_id', $service->id)
                ->where('is_active', true)
                ->count();
            
            return [
                'id' => $service->id,
                'name' => $service->name,
                'prefix' => $service->prefix,
                'next_queue' => $nextQueue ? $nextQueue->number : null,
                'active_counters' => $activeCounters,
                'total_counters' => $activeCounters,
                'status' => $servingQueue ? 'serving' : ($nextQueue ? 'waiting' : 'available')
            ];
        });

        return response()->json([
            'zone_name' => $zoneCounter->name,
            'services' => $services,
            'timestamp' => now()->toISOString()
        ]);
    }

    // Get queues data for specific zone
    public function getZoneQueues($zoneId)
    {
        $today = Carbon::today();
        
        // Get zone counter
        $zoneCounter = Counter::where('id', $zoneId)->first();
        if (!$zoneCounter) {
            return response()->json(['error' => 'Zone not found'], 404);
        }
        
        // Get all counters for this zone
        $zoneCounters = Counter::where('id', $zoneId)
            ->orWhere('name', 'like', $zoneCounter->name . '%')
            ->where('is_active', true)
            ->get();
        
        $counterIds = $zoneCounters->pluck('id');
        
        // Get queues for this zone
        $queues = [];
        
        foreach ($zoneCounters as $counter) {
            // Get current queue for this counter
            $currentQueue = Queue::where('counter_id', $counter->id)
                ->whereIn('status', ['serving', 'called'])
                ->whereDate('created_at', $today)
                ->with(['service'])
                ->orderByRaw("CASE WHEN status = 'serving' THEN 1 WHEN status = 'called' THEN 2 END")
                ->first();
            
            $queueData = [
                'counter_id' => $counter->id,
                'counter_name' => $counter->name,
                'status' => 'available',
                'queue_number' => null,
                'service_name' => null,
                'called_at' => null
            ];
            
            if ($currentQueue) {
                $queueData['status'] = $currentQueue->status;
                $queueData['queue_number'] = $currentQueue->number;
                $queueData['service_name'] = $currentQueue->service ? $currentQueue->service->name : 'Layanan';
                $queueData['called_at'] = $currentQueue->called_at ? 
                    (is_string($currentQueue->called_at) ? $currentQueue->called_at : $currentQueue->called_at->format('H:i:s')) : null;
            }
            
            $queues[] = $queueData;
        }
        
        return response()->json([
            'zone_name' => $zoneCounter->name,
            'queues' => $queues,
            'timestamp' => now()->toISOString()
        ]);
    }
}
