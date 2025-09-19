<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('instansis', function (Blueprint $table) {
        $table->foreignId('counter_id')
              ->nullable()
              ->constrained('counters') // relasi ke tabel counters
              ->cascadeOnDelete();
    });
}

public function down(): void
{
    Schema::table('instansis', function (Blueprint $table) {
        $table->dropForeign(['counter_id']);
        $table->dropColumn('counter_id');
    });
}
};
