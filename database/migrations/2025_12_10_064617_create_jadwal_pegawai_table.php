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
        Schema::create('jadwal_pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('shift')->onDelete('set null');
            $table->enum('hari', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])->nullable();
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->integer('toleransi_telat')->default(15)->comment('Toleransi keterlambatan dalam menit');
            $table->boolean('is_aktif')->default(true);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
            
            $table->index(['pegawai_id']);
            $table->index(['is_aktif']);
            $table->index(['hari']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pegawai');
    }
};
