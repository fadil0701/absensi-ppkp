<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $pegawai = Pegawai::where('email', $request->email)->first();

        if (!$pegawai || !Hash::check($request->password, $pegawai->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if ($pegawai->status !== 'aktif') {
            throw ValidationException::withMessages([
                'email' => ['Akun tidak aktif.'],
            ]);
        }

        $token = $pegawai->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'user' => [
                    'id' => $pegawai->id,
                    'nip' => $pegawai->nip,
                    'nama' => $pegawai->nama,
                    'email' => $pegawai->email,
                    'divisi' => $pegawai->divisi,
                    'jabatan' => $pegawai->jabatan,
                    'satpelkes_id' => $pegawai->satpelkes_id,
                    'satpelkes_nama' => $pegawai->satpelkes?->nama_satpelkes,
                    'role' => $pegawai->role,
                ],
            ],
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        $pegawai = $request->user();
        $pegawai->load('satpelkes');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pegawai->id,
                'nip' => $pegawai->nip,
                'nama' => $pegawai->nama,
                'email' => $pegawai->email,
                'divisi' => $pegawai->divisi,
                'jabatan' => $pegawai->jabatan,
                'satpelkes_id' => $pegawai->satpelkes_id,
                'satpelkes' => $pegawai->satpelkes ? [
                    'id' => $pegawai->satpelkes->id,
                    'nama_satpelkes' => $pegawai->satpelkes->nama_satpelkes,
                    'kode_satpelkes' => $pegawai->satpelkes->kode_satpelkes,
                    'latitude' => $pegawai->satpelkes->latitude,
                    'longitude' => $pegawai->satpelkes->longitude,
                    'radius_absensi' => $pegawai->satpelkes->radius_absensi,
                ] : null,
                'role' => $pegawai->role,
                'device_id' => $pegawai->device_id,
            ],
        ]);
    }
}

