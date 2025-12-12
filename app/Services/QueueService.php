<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\Service;

class QueueService
{

    public function addQueue($serviceId)
    {
        $number = $this->generateNumber($serviceId);

        return Queue::create([
            'service_id' => $serviceId,
            'number' => $number,
            'status' => 'waiting',
        ]);
    }

    public function generateNumber($serviceId)
    {
        $service = Service::findOrFail($serviceId);

        $lastQueue = Queue::where('service_id', $serviceId)
            ->orderByDesc('id')
            ->first();

        $currentDate = now()->format('Y-m-d');

        $lastQueueDate = $lastQueue ? $lastQueue->created_at->format('Y-m-d') : null;

        $isSameDate = $currentDate === $lastQueueDate;

        $lastQueueNumber = $lastQueue ? intval(
            substr($lastQueue->number, strlen($service->prefix) + 1)
        ) : 0;

        $padding = $service->padding ?? 0;
        
        // Jika padding = 0, tidak ada batas maksimum
        if ($padding > 0) {
            $maximumNumber = pow(10, $padding) - 1;
            $isMaximumNumber = $lastQueueNumber === $maximumNumber;
        } else {
            $isMaximumNumber = false;
        }

        if ($isSameDate && !$isMaximumNumber) {
            $newQueueNumber = $lastQueueNumber + 1;
        } else {
            $newQueueNumber = 1;
        }

        // Jika padding = 0, tidak perlu str_pad
        if ($padding == 0) {
            return $service->prefix . '-' . $newQueueNumber;
        }

        return $service->prefix . '-' . str_pad($newQueueNumber, $padding, "0", STR_PAD_LEFT);
    }

    public function getNextQueue($counterId)
    {
        $counter = Counter::findOrFail($counterId);
        
        // Ambil service IDs yang terkait dengan counter ini
        $serviceIds = $counter->service ? [$counter->service->id] : [];

        return Queue::where('status', 'waiting')
            ->whereIn('service_id', $serviceIds)
            ->where(function($query) use ($counterId) {
                $query->whereNull('counter_id')->orWhere('counter_id', $counterId);
            })
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->orderBy('id')
            ->first();
    }

    public function callNextQueue($counterId)
    {
        $nextQueue = $this->getNextQueue($counterId);

        if ($nextQueue && !$nextQueue->counter_id) {
            $nextQueue->update([
                'counter_id' => $counterId,
                'called_at' => now()
            ]);
        }

        return $nextQueue;
    }
    
    public function serveQueue(Queue $queue)
    {
        if ($queue->status !== 'waiting') {
            return;
        }

        $queue->update([
            'status' => 'serving',
            'served_at' => now()
        ]);
    }

    public function finishQueue(Queue $queue)
    {
        if ($queue->status !== 'serving') {
            return;
        }

        $queue->update([
            'status' => 'finished',
            'finished_at' => now()
        ]);
    }

    public function cancelQueue(Queue $queue)
    {
        if ($queue->status !== 'waiting') {
            return;
        }

        $queue->update([
            'status' => 'canceled',
            'canceled_at' => now()
        ]);
    }
}
