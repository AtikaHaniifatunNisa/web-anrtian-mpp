<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    public function getLatestAnnouncement(): JsonResponse
    {
        // Ambil antrian yang baru saja dipanggil (dalam 30 detik terakhir)
        $latestAnnouncement = Queue::with(['service', 'counter.instansi'])
            ->where('status', 'serving')
            ->where('called_at', '>=', now()->subSeconds(30))
            ->orderBy('called_at', 'desc')
            ->first();

        if (!$latestAnnouncement) {
            return response()->json(null);
        }

        return response()->json([
            'queueNumber' => $latestAnnouncement->number,
            'serviceName' => $latestAnnouncement->service?->name ?? 'Layanan',
            'counterName' => $latestAnnouncement->counter?->name ?? 'Loket',
            'zona' => $latestAnnouncement->counter?->instansi?->nama_instansi ?? 'Zona',
            'calledAt' => $latestAnnouncement->called_at ? 
                (is_string($latestAnnouncement->called_at) ? $latestAnnouncement->called_at : $latestAnnouncement->called_at->format('H:i:s')) : 
                now()->format('H:i:s')
        ]);
    }
}
