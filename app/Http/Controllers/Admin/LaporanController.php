<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanPresensiExport;
use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    protected LaporanService $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index(Request $request)
    {
        $query = \App\Models\Presensi::with(['pegawai', 'satpelkes'])
            ->where('status', '!=', 'REJECTED')
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_absen', 'desc');

        // Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        } else {
            $query->where('tanggal', '>=', now()->startOfMonth()->format('Y-m-d'));
        }

        if ($request->filled('tanggal_selesai')) {
            $query->where('tanggal', '<=', $request->tanggal_selesai);
        } else {
            $query->where('tanggal', '<=', now()->endOfMonth()->format('Y-m-d'));
        }

        // Filter pegawai
        if (auth('web')->user()->role === 'pegawai') {
            $query->where('pegawai_id', auth('web')->id());
        } elseif ($request->filled('pegawai_id')) {
            $query->where('pegawai_id', $request->pegawai_id);
        }

        // Filter jenis absen (berdasarkan status atau keterangan)
        if ($request->filled('jenis_absen')) {
            // Untuk jenis absen, kita perlu logic khusus karena tidak ada field langsung
            // Ini akan di-handle di view atau service jika diperlukan
        }

        $presensi = $query->paginate(15)->withQueryString();

        // Get list pegawai untuk dropdown
        $pegawaiList = \App\Models\Pegawai::aktif()->orderBy('nama')->get();

        return view('admin.laporan.index', compact('presensi', 'pegawaiList'));
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

        $filename = 'Laporan_Presensi_'.$request->tanggal_mulai.'_'.$request->tanggal_selesai.'.xlsx';

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

        $filename = 'Laporan_Presensi_'.$request->tanggal_mulai.'_'.$request->tanggal_selesai.'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Laporan akumulasi bulanan
     */
    public function akumulasi(Request $request)
    {
        // Default ke bulan ini jika tidak ada input
        $bulan = $request->filled('bulan') ? $request->bulan : now()->format('Y-m');

        $pegawaiId = auth('web')->user()->role === 'pegawai' ? auth('web')->id() : $request->pegawai_id;

        $data = $this->laporanService->getAkumulasiBulanan($bulan, $pegawaiId);

        // Get list pegawai untuk dropdown
        $pegawaiList = \App\Models\Pegawai::aktif()->orderBy('nama')->get();

        return view('admin.laporan.akumulasi', compact('data', 'pegawaiList', 'bulan'));
    }
}
