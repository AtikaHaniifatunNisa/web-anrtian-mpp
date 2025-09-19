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
                        // foreign key ke counters
            $table->foreignId('counter_id')
                  ->constrained('counters') // pastikan pakai nama tabel counters
                  ->cascadeOnDelete();
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