<?php

namespace Tests\Feature\Api;

use App\Models\Pegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success()
    {
        // Create test pegawai
        $pegawai = Pegawai::factory()->create([
            'email' => 'test@ppkp.go.id',
            'password' => bcrypt('password'),
            'status' => 'aktif',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@ppkp.go.id',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'nip',
                        'nama',
                        'email',
                    ],
                ],
            ]);
    }

    public function test_login_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'wrong@ppkp.go.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }
}


