<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST PREFIX SYSTEM ===\n\n";

// 1. Test melihat semua service dan prefix
echo "1. DAFTAR SERVICE DAN PREFIX:\n";
echo "================================\n";
$services = \App\Models\Service::where('is_active', true)->get();
foreach ($services as $service) {
    echo "ID: " . $service->id . "\n";
    echo "Nama: " . $service->name . "\n";
    echo "Prefix: " . $service->prefix . "\n";
    echo "Padding: " . $service->padding . " digit\n";
    echo "Format: " . $service->prefix . str_pad(1, $service->padding, '0', STR_PAD_LEFT) . "\n";
    echo "---\n";
}

echo "\n";

// 2. Test QueueService untuk generate nomor
echo "2. TEST GENERATE NOMOR ANTRIAN:\n";
echo "================================\n";
$queueService = new \App\Services\QueueService();

// Test untuk beberapa service
$testServices = $services->take(3);
foreach ($testServices as $service) {
    echo "Service: " . $service->name . " (Prefix: " . $service->prefix . ")\n";
    
    // Generate 3 nomor antrian
    for ($i = 1; $i <= 3; $i++) {
        $number = $queueService->generateNumber($service->id);
        echo "  Antrian ke-" . $i . ": " . $number . "\n";
    }
    echo "\n";
}

echo "\n";

// 3. Test melihat antrian yang sudah ada
echo "3. ANTRIAN YANG SUDAH ADA:\n";
echo "==========================\n";
$existingQueues = \App\Models\Queue::with('service')->orderBy('created_at', 'desc')->take(10)->get();
foreach ($existingQueues as $queue) {
    echo "Nomor: " . $queue->number . "\n";
    echo "Service: " . ($queue->service?->name ?? 'Unknown') . "\n";
    echo "Prefix: " . ($queue->service?->prefix ?? 'Unknown') . "\n";
    echo "Status: " . $queue->status . "\n";
    echo "Tanggal: " . $queue->created_at->format('Y-m-d H:i:s') . "\n";
    echo "---\n";
}

echo "\n";

// 4. Test membuat antrian baru
echo "4. TEST MEMBUAT ANTRIAN BARU:\n";
echo "=============================\n";
$testService = $services->first();
if ($testService) {
    echo "Membuat antrian untuk service: " . $testService->name . "\n";
    echo "Prefix: " . $testService->prefix . "\n";
    echo "Padding: " . $testService->padding . " digit\n";
    
    try {
        $newQueue = $queueService->addQueue($testService->id);
        echo "✅ Antrian berhasil dibuat!\n";
        echo "Nomor antrian: " . $newQueue->number . "\n";
        echo "Status: " . $newQueue->status . "\n";
        echo "Service ID: " . $newQueue->service_id . "\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Tidak ada service untuk di-test\n";
}

echo "\n";

// 5. Test analisis prefix
echo "5. ANALISIS PREFIX:\n";
echo "===================\n";
$prefixCounts = [];
foreach ($services as $service) {
    $prefix = $service->prefix;
    if (!isset($prefixCounts[$prefix])) {
        $prefixCounts[$prefix] = 0;
    }
    $prefixCounts[$prefix]++;
}

echo "Distribusi Prefix:\n";
foreach ($prefixCounts as $prefix => $count) {
    echo "  " . $prefix . ": " . $count . " service(s)\n";
}

echo "\n";

// 6. Test format nomor
echo "6. TEST FORMAT NOMOR:\n";
echo "=====================\n";
foreach ($services->take(5) as $service) {
    echo "Service: " . $service->name . "\n";
    echo "Prefix: " . $service->prefix . "\n";
    echo "Padding: " . $service->padding . "\n";
    
    // Simulasi nomor 1-5
    for ($i = 1; $i <= 5; $i++) {
        $formatted = $service->prefix . str_pad($i, $service->padding, '0', STR_PAD_LEFT);
        echo "  " . $i . " -> " . $formatted . "\n";
    }
    echo "\n";
}

echo "\n";

// 7. Test prefix validation
echo "7. TEST VALIDASI PREFIX:\n";
echo "========================\n";
$duplicatePrefixes = [];
$prefixes = [];
foreach ($services as $service) {
    if (in_array($service->prefix, $prefixes)) {
        $duplicatePrefixes[] = $service->prefix;
    }
    $prefixes[] = $service->prefix;
}

if (empty($duplicatePrefixes)) {
    echo "✅ Semua prefix unik - tidak ada duplikasi\n";
} else {
    echo "❌ Ada prefix duplikasi: " . implode(', ', $duplicatePrefixes) . "\n";
}

echo "\n";

// 8. Test prefix length
echo "8. ANALISIS PANJANG PREFIX:\n";
echo "===========================\n";
$prefixLengths = [];
foreach ($services as $service) {
    $length = strlen($service->prefix);
    if (!isset($prefixLengths[$length])) {
        $prefixLengths[$length] = 0;
    }
    $prefixLengths[$length]++;
}

foreach ($prefixLengths as $length => $count) {
    echo "Panjang " . $length . " karakter: " . $count . " service(s)\n";
}

echo "\n";

// 9. Test prefix pattern
echo "9. ANALISIS POLA PREFIX:\n";
echo "========================\n";
$patterns = [];
foreach ($services as $service) {
    $prefix = $service->prefix;
    $pattern = preg_replace('/[0-9]/', 'X', $prefix);
    if (!isset($patterns[$pattern])) {
        $patterns[$pattern] = [];
    }
    $patterns[$pattern][] = $prefix;
}

foreach ($patterns as $pattern => $prefixes) {
    echo "Pola " . $pattern . ": " . implode(', ', $prefixes) . "\n";
}

echo "\n";

// 10. Summary
echo "10. SUMMARY:\n";
echo "============\n";
echo "Total Service Aktif: " . $services->count() . "\n";
echo "Total Prefix Unik: " . count(array_unique($prefixes)) . "\n";
echo "Prefix Terpanjang: " . max(array_map('strlen', $prefixes)) . " karakter\n";
echo "Prefix Terpendek: " . min(array_map('strlen', $prefixes)) . " karakter\n";

echo "\n=== TEST PREFIX SELESAI ===\n";
