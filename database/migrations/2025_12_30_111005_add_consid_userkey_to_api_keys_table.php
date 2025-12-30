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
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('consid', 64)->nullable()->after('api_key')->comment('Consumer ID untuk integrasi');
            $table->string('userkey', 64)->nullable()->after('consid')->comment('User Key untuk integrasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn(['consid', 'userkey']);
        });
    }
};
