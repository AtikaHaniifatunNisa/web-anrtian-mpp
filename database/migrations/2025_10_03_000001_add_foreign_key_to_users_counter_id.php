<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan index untuk performa
            $table->index('counter_id');

            // Tambahkan foreign key constraint (kolom sudah ada)
            $table->foreign('counter_id')
                ->references('id')
                ->on('counters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['counter_id']);
            $table->dropIndex(['counter_id']);
        });
    }
};


