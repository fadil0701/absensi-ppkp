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
        Schema::create('presensi_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presensi_id')->constrained('presensi')->onDelete('cascade');
            $table->foreignId('pimpinan_id')->constrained('pegawai')->onDelete('cascade');
            $table->enum('action', ['approve', 'reject']);
            $table->text('catatan')->nullable();
            $table->timestamp('waktu_action')->useCurrent();
            
            $table->index(['presensi_id']);
            $table->index(['pimpinan_id']);
            $table->index(['waktu_action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_log');
    }
};
