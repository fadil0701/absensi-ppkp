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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama aplikasi/sistem yang menggunakan API Key');
            $table->string('api_key', 64)->unique()->comment('API Key (public identifier)');
            $table->string('secret_key', 64)->comment('Secret Key (untuk signing/verification)');
            $table->text('description')->nullable()->comment('Deskripsi penggunaan API Key');
            $table->string('webhook_url')->nullable()->comment('URL webhook untuk notifikasi');
            $table->json('allowed_ips')->nullable()->comment('IP whitelist (optional)');
            $table->json('scopes')->nullable()->comment('Permission scopes');
            $table->integer('rate_limit')->default(60)->comment('Rate limit per menit');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['api_key', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
