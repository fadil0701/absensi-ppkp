<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        // Riwayat Presensi termasuk Riwayat Tugas Luar (presensi dengan keterangan tugas luar)
        $query = Presensi::with(['pegawai', 'satpelkes']);

        if ($request->search) {
            $query->whereHas('pegawai', function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

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

        // Filter untuk tugas luar
        if ($request->has('tugas_luar')) {
            if ($request->tugas_luar == '1') {
                // Hanya tampilkan presensi tugas luar
                $query->whereNotNull('keterangan')
                      ->where('keterangan', 'like', '%tugas luar%');
            } elseif ($request->tugas_luar == '0') {
                // Hanya tampilkan presensi rutin
                $query->where(function($q) {
                    $q->whereNull('keterangan')
                      ->orWhere('keterangan', 'not like', '%tugas luar%');
                });
            }
        }

        // Admin & Pimpinan: lihat semua presensi seluruh pegawai
        // Pegawai: hanya lihat presensi sendiri
        if (auth('web')->user()->role === 'pegawai') {
            $query->where('pegawai_id', auth('web')->id());
        }

        $presensi = $query->latest('waktu_absen')->paginate(10);

        return view('admin.presensi.index', compact('presensi'));
    }

    public function show($id)
    {
        $presensi = Presensi::with(['pegawai', 'satpelkes', 'presensiLog.pimpinan'])->findOrFail($id);

        // Check authorization
        if (auth('web')->user()->role === 'pegawai' && $presensi->pegawai_id !== auth('web')->id()) {
            abort(403);
        }
        
        return view('admin.presensi.show', compact('presensi'));
    }
}

