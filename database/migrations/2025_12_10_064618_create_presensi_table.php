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
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('jenis', ['check_in', 'check_out']);
            $table->dateTime('waktu_absen');
            
            // GPS Data
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 10, 2)->comment('Akurasi GPS dalam meter');
            $table->string('device_id');
            
            // Lokasi & Zona
            $table->foreignId('satpelkes_id')->nullable()->constrained('satpelkes')->onDelete('set null');
            $table->decimal('jarak_ke_satpelkes', 10, 2)->nullable()->comment('Jarak ke satpelkes dalam meter');
            $table->enum('status', ['IN_ZONE', 'OUT_ZONE_PENDING', 'APPROVED', 'REJECTED'])->default('IN_ZONE');
            
            // Foto
            $table->string('foto_asli')->nullable()->comment('Path foto original');
            $table->string('foto_watermark')->nullable()->comment('Path foto dengan watermark');
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
            
            $table->index(['pegawai_id', 'tanggal']);
            $table->index(['status']);
            $table->index(['tanggal']);
            $table->index(['satpelkes_id']);
            $table->index(['jenis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
