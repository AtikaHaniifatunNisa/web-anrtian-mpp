<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Instansi;
use Illuminate\Http\Request;

class QueueKioskController extends Controller
{
    public function index()
    {
        // Ambil semua zona (counter) dengan instansinya
        $counters = Counter::with('instansi')->get();

        return view('queue-kiosk.index', compact('counters'));
    }

    public function showInstansi(Counter $counter)
    {
        // Ambil semua instansi di dalam zona
        $instansi = $counter->instansi();

        return view('queue-kiosk.instansi', compact('counter', 'instansi'));
    }

    public function showServices(Instansi $instansi)
    {
        // Ambil semua layanan dalam instansi
        $services = $instansi->services;

        return view('queue-kiosk.services', compact('instansi', 'services'));
    }
}
