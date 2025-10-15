<?php

use App\Filament\Pages\QueueStatus;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\QueueKioskController;
use App\Exports\RekapLayananExport;
use App\Filament\Pages\AntrianSkckBerjalanPage;
use App\Filament\Pages\AntrianSkckPage;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\StrukController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\TvDisplayController;

Route::get('queue-status', QueueStatus::class)->name('queue.status');

Route::middleware(['auth']) // atau middleware panel kamu sendiri jika pakai multi-panel
    ->get('/exports/rekap-layanan', [ExportController::class, 'rekapLayanan'])
    ->name('export.rekap-layanan');

Route::get('/export/rekap-jumlah-pemohon', function (\Illuminate\Http\Request $request) {
    $from = $request->query('from', now()->toDateString());
    $to   = $request->query('to', now()->toDateString());

    return Excel::download(new RekapLayananExport($from, $to), 'rekap_jumlah_pemohon.xlsx');
})->name('export.rekap-jumlah-pemohon');


Route::get('/antrian-skck-mpp', AntrianSkckPage::class);
Route::get('/antrian-skck-mpp/terdaftar', AntrianSkckBerjalanPage::class);
Route::get('/antrian-skck-mpp/{id}',[ ExportController::class, 'cetakSkck']);

Route::get('/tampilan-tv', function () {
    return view('tampilan_tv');
});

// PDF tiket antrian
Route::get('/tickets/{queue}/pdf', [TicketController::class, 'queuePdf'])->name('tickets.pdf');

// PDF struk antrian
Route::get('/struk/generate', [StrukController::class, 'generateStruk'])->name('struk.generate');
Route::get('/struk/preview', [StrukController::class, 'previewStruk'])->name('struk.preview');
Route::get('/struk/test', function() {
    return view('pdf-preview', ['serviceId' => 1, 'zona' => 'Zona 1']);
})->name('struk.test');

// Barcode antrian
Route::get('/barcode/show', [BarcodeController::class, 'show'])->name('barcode.show');
Route::get('/barcode/scan', [BarcodeController::class, 'scan'])->name('barcode.scan');

// TV Display dan Announcement
Route::get('/tv-display', function() {
    return view('tv-display');
})->name('tv.display');

Route::get('/tv-display-enhanced', function() {
    return view('tv-display-enhanced');
})->name('tv.display.enhanced');

Route::get('/tv-display-optimized', function() {
    return view('tv-display-optimized');
})->name('tv.display.optimized');
Route::get('/api/announcements/latest', [AnnouncementController::class, 'getLatestAnnouncement'])->name('api.announcements.latest');
Route::get('/api/tv-display/queue-status', [TvDisplayController::class, 'getQueueStatus'])->name('api.tv.queue-status');
Route::get('/api/tv-display/latest-announcement', [TvDisplayController::class, 'getLatestAnnouncement'])->name('api.tv.latest-announcement');

// TV Display Index (Public - No Auth Required)
Route::get('/tv-display', function() {
    return view('tv-display-index');
})->name('tv.display.index');

// TV Display per Zona (Public - No Auth Required)
Route::get('/tv-display/zona/{zoneId}', function($zoneId) {
    $zoneCounter = \App\Models\Counter::where('id', $zoneId)->first();
    if (!$zoneCounter) {
        abort(404, 'Zone not found');
    }
    
    return view('tv-display-zone', [
        'zoneId' => $zoneId,
        'zoneName' => $zoneCounter->name
    ]);
})->name('tv.display.zone');

// TV Display Public Routes (Short URLs for easy access)
Route::get('/tv', function() {
    return view('tv-landing');
})->name('tv.index');

// Redirect root to admin login
Route::get('/', function() {
    return redirect('/admin');
})->name('home');

// Simple TV Display (Direct access without login)
Route::get('/tv-simple', function() {
    return view('tv-simple', [
        'zoneId' => 5,
        'zoneName' => 'ZONA 1'
    ]);
})->name('tv.simple');

Route::get('/tv1', function() {
    $zoneCounter = \App\Models\Counter::where('id', 5)->first();
    return view('tv-simple', [
        'zoneId' => 5,
        'zoneName' => $zoneCounter->name
    ]);
})->name('tv.zona1');

Route::get('/tv2', function() {
    $zoneCounter = \App\Models\Counter::where('id', 20)->first();
    return view('tv-simple', [
        'zoneId' => 20,
        'zoneName' => $zoneCounter->name
    ]);
})->name('tv.zona2');

Route::get('/tv3', function() {
    $zoneCounter = \App\Models\Counter::where('id', 29)->first();
    return view('tv-simple', [
        'zoneId' => 29,
        'zoneName' => $zoneCounter->name
    ]);
})->name('tv.zona3');

Route::get('/tv4', function() {
    $zoneCounter = \App\Models\Counter::where('id', 40)->first();
    return view('tv-simple', [
        'zoneId' => 40,
        'zoneName' => $zoneCounter->name
    ]);
})->name('tv.zona4');

Route::get('/tv5', function() {
    $zoneCounter = \App\Models\Counter::where('id', 109)->first();
    return view('tv-simple', [
        'zoneId' => 109,
        'zoneName' => $zoneCounter->name
    ]);
})->name('tv.zona5');

// API untuk data per zona
Route::get('/api/tv-display/zone/{zoneId}/services', [TvDisplayController::class, 'getZoneServices'])->name('api.tv.zone.services');
Route::get('/api/tv-display/zone/{zoneId}/queues', [TvDisplayController::class, 'getZoneQueues'])->name('api.tv.zone.queues');

// Audio API
Route::get('/api/audio/announcement', [AudioController::class, 'getAnnouncementAudio'])->name('api.audio.announcement');
Route::post('/api/audio/upload', [AudioController::class, 'uploadAudio'])->name('api.audio.upload');
Route::get('/api/audio/list', [AudioController::class, 'getAudioList'])->name('api.audio.list');
Route::delete('/api/audio/delete', [AudioController::class, 'deleteAudio'])->name('api.audio.delete');