<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    /**
     * Get jadwal pegawai
     */
    public function getJadwal(Request $request, int $pegawaiId)
    {
        // Cek authorization
        if ($request->user()->id !== $pegawaiId && !$request->user()->isPimpinan()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $jadwal = JadwalPegawai::where('pegawai_id', $pegawaiId)
            ->aktif()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'pegawai_id' => $pegawaiId,
                'pegawai_nama' => \App\Models\Pegawai::find($pegawaiId)->nama,
                'jadwal' => $jadwal->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'hari' => $item->hari,
                        'jam_masuk' => $item->jam_masuk,
                        'jam_keluar' => $item->jam_keluar,
                        'toleransi_telat' => $item->toleransi_telat,
                        'is_aktif' => $item->is_aktif,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Create/Update jadwal pegawai
     */
    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|integer|exists:pegawai,id',
            'jadwal' => 'required|array',
            'jadwal.*.hari' => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jadwal.*.jam_masuk' => 'required|date_format:H:i:s',
            'jadwal.*.jam_keluar' => 'required|date_format:H:i:s|after:jadwal.*.jam_masuk',
            'jadwal.*.toleransi_telat' => 'nullable|integer|min:0',
        ]);

        // Cek authorization (hanya admin/pimpinan)
        if (!$request->user()->isPimpinan()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Hapus jadwal lama (optional, atau bisa di-update)
            JadwalPegawai::where('pegawai_id', $request->pegawai_id)->delete();

            // Insert jadwal baru
            foreach ($request->jadwal as $jadwalItem) {
                JadwalPegawai::create([
                    'pegawai_id' => $request->pegawai_id,
                    'hari' => $jadwalItem['hari'] ?? null,
                    'jam_masuk' => $jadwalItem['jam_masuk'],
                    'jam_keluar' => $jadwalItem['jam_keluar'],
                    'toleransi_telat' => $jadwalItem['toleransi_telat'] ?? 15,
                    'is_aktif' => true,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil disimpan',
                'data' => [
                    'pegawai_id' => $request->pegawai_id,
                    'total_jadwal' => count($request->jadwal),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

}

