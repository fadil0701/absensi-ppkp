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
        Schema::create('izin_cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('jenis', ['izin', 'cuti'])->default('izin');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pegawai')->onDelete('set null');
            $table->timestamp('waktu_persetujuan')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();
            
            $table->index(['pegawai_id', 'tanggal']);
            $table->index(['status']);
            $table->index(['tanggal']);
            $table->index(['jenis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_cuti');
    }
};
