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
        Schema::create('satpelkes', function (Blueprint $table) {
            $table->id();
            $table->string('nama_satpelkes');
            $table->string('kode_satpelkes')->unique();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius_absensi')->default(100)->comment('Radius absensi dalam meter');
            $table->text('alamat')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
            
            $table->index(['is_aktif']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satpelkes');
    }
};


