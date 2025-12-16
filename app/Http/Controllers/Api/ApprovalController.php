<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\PresensiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApprovalController extends Controller
{
    /**
     * List pending approval
     */
    public function pending(Request $request)
    {
        $query = Presensi::pending()
            ->with(['pegawai', 'satpelkes']);

        if ($request->tanggal_mulai) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->tanggal_selesai) {
            $query->where('tanggal', '<=', $request->tanggal_selesai);
        }

        if ($request->satpelkes_id) {
            $query->where('satpelkes_id', $request->satpelkes_id);
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
                'pending' => $presensi->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'pegawai' => [
                            'id' => $item->pegawai->id,
                            'nip' => $item->pegawai->nip,
                            'nama' => $item->pegawai->nama,
                            'divisi' => $item->pegawai->divisi,
                            'jabatan' => $item->pegawai->jabatan,
                        ],
                        'tanggal' => $item->tanggal->format('Y-m-d'),
                        'jenis' => $item->jenis,
                        'waktu_absen' => $item->waktu_absen->format('Y-m-d H:i:s'),
                        'satpelkes_terdekat' => $item->satpelkes ? [
                            'id' => $item->satpelkes->id,
                            'nama_satpelkes' => $item->satpelkes->nama_satpelkes,
                            'jarak' => $item->jarak_ke_satpelkes,
                            'radius' => $item->satpelkes->radius_absensi,
                        ] : null,
                        'foto_watermark' => $item->foto_watermark,
                        'gps' => [
                            'latitude' => $item->latitude,
                            'longitude' => $item->longitude,
                            'accuracy' => $item->accuracy,
                        ],
                        'device_id' => $item->device_id,
                        'keterangan' => $item->keterangan,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Approve presensi
     */
    public function approve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'presensi_id' => 'required|integer|exists:presensi,id',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $pimpinan = $request->user();

            DB::select('CALL sp_approve_presensi(?, ?, ?, ?)', [
                $request->presensi_id,
                $pimpinan->id,
                'approve',
                $request->catatan,
            ]);

            $presensi = Presensi::findOrFail($request->presensi_id);

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil di-approve',
                'data' => [
                    'presensi_id' => $presensi->id,
                    'status' => $presensi->status,
                    'approved_by' => [
                        'id' => $pimpinan->id,
                        'nama' => $pimpinan->nama,
                    ],
                    'waktu_approval' => now()->format('Y-m-d H:i:s'),
                    'catatan' => $request->catatan,
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
     * Reject presensi
     */
    public function reject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'presensi_id' => 'required|integer|exists:presensi,id',
            'catatan' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $pimpinan = $request->user();

            DB::select('CALL sp_approve_presensi(?, ?, ?, ?)', [
                $request->presensi_id,
                $pimpinan->id,
                'reject',
                $request->catatan,
            ]);

            $presensi = Presensi::findOrFail($request->presensi_id);

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil di-reject',
                'data' => [
                    'presensi_id' => $presensi->id,
                    'status' => $presensi->status,
                    'rejected_by' => [
                        'id' => $pimpinan->id,
                        'nama' => $pimpinan->nama,
                    ],
                    'waktu_rejection' => now()->format('Y-m-d H:i:s'),
                    'catatan' => $request->catatan,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

