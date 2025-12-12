<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'ZONA 1', 'ZONA 2'
            $table->boolean('is_active')->default(true); // Status aktif/tidak
            // instansi_id akan ditambahkan di migration terpisah setelah tabel instansis dibuat
            // Migration: 2025_09_03_091320_add_instansi_id_to_counters_table.php
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counters');
    }
};