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
        Schema::create('shift', function (Blueprint $table) {
            $table->id();
            $table->string('nama_shift');
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->integer('toleransi_telat')->default(15)->comment('Toleransi keterlambatan dalam menit');
            $table->boolean('is_default')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index(['is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift');
    }
};
