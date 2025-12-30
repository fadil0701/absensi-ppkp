<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IntegrationController extends Controller
{
    protected LaporanService $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    /**
     * Get akumulasi laporan untuk integrasi
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAkumulasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulan' => 'required|date_format:Y-m',
            'pegawai_id' => 'nullable|exists:pegawai,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $this->laporanService->getAkumulasiBulanan(
            $request->bulan,
            $request->pegawai_id
        );

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'bulan' => $request->bulan,
                'total_pegawai' => count($data),
            ],
        ]);
    }

    /**
     * Get presensi harian untuk integrasi
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPresensiHarian(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'pegawai_id' => 'nullable|exists:pegawai,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = DB::table('presensi as p')
            ->join('pegawai as pg', 'p.pegawai_id', '=', 'pg.id')
            ->leftJoin('satpelkes as s', 'p.satpelkes_id', '=', 's.id')
            ->where('p.tanggal', $request->tanggal)
            ->whereIn('p.status', ['IN_ZONE', 'APPROVED'])
            ->select([
                'p.id',
                'p.pegawai_id',
                'pg.nip',
                'pg.nama',
                'pg.jabatan',
                's.nama_satpelkes as unit_kerja',
                'p.jenis',
                'p.waktu_absen',
                'p.status',
            ]);

        if ($request->pegawai_id) {
            $query->where('p.pegawai_id', $request->pegawai_id);
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'tanggal' => $request->tanggal,
                'total' => $data->count(),
            ],
        ]);
    }

    /**
     * Export data ke format yang bisa di-import sistem lain
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:json,csv',
            'type' => 'required|in:presensi,akumulasi,laporan',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Implementasi export berdasarkan type dan format
        // Ini adalah placeholder, bisa dikembangkan lebih lanjut

        return response()->json([
            'success' => true,
            'message' => 'Export feature akan diimplementasikan',
        ]);
    }
}
