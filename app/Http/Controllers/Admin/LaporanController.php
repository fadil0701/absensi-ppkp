<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use App\Exports\LaporanPresensiExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    protected LaporanService $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index()
    {
        return view('admin.laporan.index');
    }

    public function telat(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $pegawaiId = auth('web')->user()->role === 'pegawai' ? auth('web')->id() : $request->pegawai_id;

        $data = $this->laporanService->getLaporanTelat(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
            $pegawaiId
        );

        return view('admin.laporan.telat', compact('data'));
    }

    public function tidakMasuk(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $pegawaiId = auth('web')->user()->role === 'pegawai' ? auth('web')->id() : $request->pegawai_id;

        $data = $this->laporanService->getLaporanTidakMasuk(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
            $pegawaiId
        );

        return view('admin.laporan.tidakMasuk', compact('data'));
    }

    /**
     * Export semua laporan presensi ke Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_absen' => 'nullable|string|in:Rutin,Tidak Masuk,Izin,Cuti,Sakit,Tugas Luar',
        ]);

        $pegawaiId = auth('web')->user()->role === 'pegawai' ? auth('web')->id() : $request->pegawai_id;

        $data = $this->laporanService->getAllPresensiForExport(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
            $pegawaiId,
            $request->jenis_absen
        );

        $filename = 'Laporan_Presensi_' . $request->tanggal_mulai . '_' . $request->tanggal_selesai . '.xlsx';

        return Excel::download(new LaporanPresensiExport($data), $filename);
    }

    /**
     * Export semua laporan presensi ke PDF
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_absen' => 'nullable|string|in:Rutin,Tidak Masuk,Izin,Cuti,Sakit,Tugas Luar',
        ]);

        $pegawaiId = auth('web')->user()->role === 'pegawai' ? auth('web')->id() : $request->pegawai_id;

        $data = $this->laporanService->getAllPresensiForExport(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
            $pegawaiId,
            $request->jenis_absen
        );

        $tanggalMulai = \Carbon\Carbon::parse($request->tanggal_mulai)->format('d F Y');
        $tanggalSelesai = \Carbon\Carbon::parse($request->tanggal_selesai)->format('d F Y');

        $pdf = Pdf::loadView('admin.laporan.export-pdf', [
            'data' => $data,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
        ])->setPaper('a4', 'landscape');

        $filename = 'Laporan_Presensi_' . $request->tanggal_mulai . '_' . $request->tanggal_selesai . '.pdf';

        return $pdf->download($filename);
    }
}

