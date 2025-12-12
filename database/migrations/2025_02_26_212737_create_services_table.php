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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('prefix');
            $table->integer('padding');
            $table->boolean('is_active')->default(true);
            // instansi_id akan ditambahkan di migration terpisah setelah tabel instansis dibuat
            // Migration: 2025_09_15_025713_add_instansi_id_to_services_table.php
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
