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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('divisi')->nullable();
            $table->string('jabatan')->nullable();
            $table->foreignId('satpelkes_id')->nullable()->constrained('satpelkes')->onDelete('set null');
            $table->string('device_id')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->enum('role', ['admin', 'pimpinan', 'pegawai'])->default('pegawai');
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['role']);
            $table->index(['satpelkes_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
