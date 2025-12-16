<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    protected LaporanService $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    /**
     * Laporan telat
     */
    public function telat(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $pegawaiId = $request->user()->isPimpinan() ? $request->pegawai_id : $request->user()->id;

        $data = $this->laporanService->getLaporanTelat(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
            $pegawaiId
        );

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => [
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                ],
                'total_telat' => count($data),
                'pegawai_telat' => $data,
            ],
        ]);
    }

    /**
     * Laporan tidak masuk
     */
    public function tidakMasuk(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $pegawaiId = $request->user()->isPimpinan() ? $request->pegawai_id : $request->user()->id;

        $data = $this->laporanService->getLaporanTidakMasuk(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
            $pegawaiId
        );

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => [
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                ],
                'total_tidak_masuk' => count($data),
                'pegawai_tidak_masuk' => $data,
            ],
        ]);
    }

    /**
     * Dashboard statistik
     */
    public function dashboard(Request $request)
    {
        $pegawai = $request->user();
        $bulan = $request->bulan ?? now()->format('Y-m');

        $statistik = $this->laporanService->getStatistikDashboard($pegawai->id, $bulan);

        return response()->json([
            'success' => true,
            'data' => $statistik,
        ]);
    }
}


