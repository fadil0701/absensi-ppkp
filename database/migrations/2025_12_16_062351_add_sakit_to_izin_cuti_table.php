<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mengubah enum jenis untuk menambahkan 'sakit'
        DB::statement("ALTER TABLE izin_cuti MODIFY COLUMN jenis ENUM('izin', 'cuti', 'sakit') DEFAULT 'izin'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum semula (tanpa sakit)
        DB::statement("ALTER TABLE izin_cuti MODIFY COLUMN jenis ENUM('izin', 'cuti') DEFAULT 'izin'");
    }
};
