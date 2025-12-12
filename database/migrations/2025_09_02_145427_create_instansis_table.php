<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instansis', function (Blueprint $table) {
            $table->id('instansi_id');
            $table->string('nama_instansi');
            $table->text('deskripsi')->nullable();
            // counter_id akan ditambahkan di migration terpisah setelah tabel counters dibuat
            // Migration: 2025_09_19_011552_add_counter_id_to_instansis_table.php
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instansis');
    }
};