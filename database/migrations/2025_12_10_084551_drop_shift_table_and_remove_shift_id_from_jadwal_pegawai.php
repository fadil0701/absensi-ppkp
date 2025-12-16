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
        // Hapus foreign key constraint dari jadwal_pegawai
        Schema::table('jadwal_pegawai', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        // Hapus kolom shift_id dari jadwal_pegawai
        Schema::table('jadwal_pegawai', function (Blueprint $table) {
            $table->dropColumn('shift_id');
        });

        // Hapus tabel shift
        Schema::dropIfExists('shift');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate shift table
        Schema::create('shift', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shift');
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->integer('toleransi_telat')->default(15);
            $table->boolean('is_default')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index(['is_default']);
        });

        // Add shift_id back to jadwal_pegawai
        Schema::table('jadwal_pegawai', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('pegawai_id')->constrained('shift')->onDelete('set null');
        });
    }
};
