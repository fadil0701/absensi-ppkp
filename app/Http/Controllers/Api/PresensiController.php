<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Services\PresensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PresensiController extends Controller
{
    protected PresensiService $presensiService;

    public function __construct(PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    /**
     * Check-in
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0|max:100',
            'device_id' => 'required|string',
            'foto' => 'required|string', // base64
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $pegawai = $request->user();

            $result = $this->presensiService->processCheckIn(
                $pegawai->id,
                'check_in',
                $request->latitude,
                $request->longitude,
                $request->accuracy,
                $request->device_id,
                $request->foto,
                $request->keterangan
            );

            $presensi = Presensi::find($result['presensi_id']);
            $presensi->load('satpelkes');

            $message = $result['status'] === 'IN_ZONE' 
                ? 'Check-in berhasil' 
                : 'Check-in berhasil, menunggu approval pimpinan';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'presensi_id' => $result['presensi_id'],
                    'tanggal' => $presensi->tanggal->format('Y-m-d'),
                    'waktu_absen' => $presensi->waktu_absen->format('Y-m-d H:i:s'),
                    'jenis' => $presensi->jenis,
                    'status' => $presensi->status,
                    'satpelkes' => $presensi->satpelkes ? [
                        'id' => $presensi->satpelkes->id,
                        'nama_satpelkes' => $presensi->satpelkes->nama_satpelkes,
                        'kode_satpelkes' => $presensi->satpelkes->kode_satpelkes,
                        'jarak' => $result['jarak'],
                    ] : null,
                    'satpelkes_terdekat' => $presensi->satpelkes ? [
                        'id' => $presensi->satpelkes->id,
                        'nama_satpelkes' => $presensi->satpelkes->nama_satpelkes,
                        'jarak' => $result['jarak'],
                        'radius' => $presensi->satpelkes->radius_absensi,
                    ] : null,
                    'foto_watermark' => $result['foto_watermark'],
                    'gps' => [
                        'latitude' => $presensi->latitude,
                        'longitude' => $presensi->longitude,
                        'accuracy' => $presensi->accuracy,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check-out
     */
    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0|max:100',
            'device_id' => 'required|string',
            'foto' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $pegawai = $request->user();

            $result = $this->presensiService->processCheckIn(
                $pegawai->id,
                'check_out',
                $request->latitude,
                $request->longitude,
                $request->accuracy,
                $request->device_id,
                $request->foto,
                $request->keterangan
            );

            $presensi = Presensi::find($result['presensi_id']);
            $presensi->load('satpelkes');

            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil',
                'data' => [
                    'presensi_id' => $result['presensi_id'],
                    'tanggal' => $presensi->tanggal->format('Y-m-d'),
                    'waktu_absen' => $presensi->waktu_absen->format('Y-m-d H:i:s'),
                    'jenis' => $presensi->jenis,
                    'status' => $presensi->status,
                    'satpelkes' => $presensi->satpelkes ? [
                        'id' => $presensi->satpelkes->id,
                        'nama_satpelkes' => $presensi->satpelkes->nama_satpelkes,
                        'jarak' => $result['jarak'],
                    ] : null,
                    'foto_watermark' => $result['foto_watermark'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Riwayat presensi
     */
    public function riwayat(Request $request)
    {
        $pegawai = $request->user();

        $query = Presensi::where('pegawai_id', $pegawai->id)
            ->with('satpelkes');

        if ($request->tanggal_mulai) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->tanggal_selesai) {
            $query->where('tanggal', '<=', $request->tanggal_selesai);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->jenis) {
            $query->where('jenis', $request->jenis);
        }

        $presensi = $query->orderBy('tanggal', 'desc')
            ->orderBy('waktu_absen', 'desc')
            ->paginate($request->limit ?? 50);

        return response()->json([
            'success' => true,
            'data' => [
                'current_page' => $presensi->currentPage(),
                'per_page' => $presensi->perPage(),
                'total' => $presensi->total(),
                'last_page' => $presensi->lastPage(),
                'presensi' => $presensi->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'tanggal' => $item->tanggal->format('Y-m-d'),
                        'jenis' => $item->jenis,
                        'waktu_absen' => $item->waktu_absen->format('Y-m-d H:i:s'),
                        'status' => $item->status,
                        'satpelkes' => $item->satpelkes ? [
                            'id' => $item->satpelkes->id,
                            'nama_satpelkes' => $item->satpelkes->nama_satpelkes,
                            'jarak' => $item->jarak_ke_satpelkes,
                        ] : null,
                        'foto_watermark' => $item->foto_watermark,
                        'gps' => [
                            'latitude' => $item->latitude,
                            'longitude' => $item->longitude,
                            'accuracy' => $item->accuracy,
                        ],
                    ];
                }),
            ],
        ]);
    }

    /**
     * Detail presensi
     */
    public function show(Request $request, int $id)
    {
        $pegawai = $request->user();
        $presensi = Presensi::with(['pegawai', 'satpelkes', 'presensiLog.pimpinan'])
            ->findOrFail($id);

        // Cek authorization (hanya bisa lihat sendiri atau pimpinan)
        if ($presensi->pegawai_id !== $pegawai->id && !$pegawai->isPimpinan()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $presensi->id,
                'pegawai' => [
                    'id' => $presensi->pegawai->id,
                    'nip' => $presensi->pegawai->nip,
                    'nama' => $presensi->pegawai->nama,
                ],
                'tanggal' => $presensi->tanggal->format('Y-m-d'),
                'jenis' => $presensi->jenis,
                'waktu_absen' => $presensi->waktu_absen->format('Y-m-d H:i:s'),
                'status' => $presensi->status,
                'satpelkes' => $presensi->satpelkes ? [
                    'id' => $presensi->satpelkes->id,
                    'nama_satpelkes' => $presensi->satpelkes->nama_satpelkes,
                    'kode_satpelkes' => $presensi->satpelkes->kode_satpelkes,
                    'jarak' => $presensi->jarak_ke_satpelkes,
                ] : null,
                'foto_asli' => $presensi->foto_asli,
                'foto_watermark' => $presensi->foto_watermark,
                'gps' => [
                    'latitude' => $presensi->latitude,
                    'longitude' => $presensi->longitude,
                    'accuracy' => $presensi->accuracy,
                ],
                'device_id' => $presensi->device_id,
                'ip_address' => $presensi->ip_address,
                'keterangan' => $presensi->keterangan,
                'log' => $presensi->presensiLog->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'pimpinan' => [
                            'id' => $log->pimpinan->id,
                            'nama' => $log->pimpinan->nama,
                        ],
                        'action' => $log->action,
                        'catatan' => $log->catatan,
                        'waktu_action' => $log->waktu_action->format('Y-m-d H:i:s'),
                    ];
                }),
            ],
        ]);
    }
}

