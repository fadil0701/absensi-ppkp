<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IzinCuti;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Carbon\Carbon;

class IzinCutiController extends Controller
{
    /**
     * Tampilkan form untuk membuat izin/cuti dari laporan tidak masuk
     */
    public function create(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'tanggal' => 'required|date',
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);
        $tanggal = Carbon::parse($request->tanggal);

        // Cek apakah sudah ada izin/cuti untuk tanggal ini
        $existing = IzinCuti::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $tanggal->format('Y-m-d'))
            ->first();

        return view('admin.izin-cuti.create', compact('pegawai', 'tanggal', 'existing'));
    }

    /**
     * Simpan izin/cuti baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:izin,cuti,sakit',
            'keterangan' => 'nullable|string',
        ]);

        // Cek apakah sudah ada izin/cuti untuk tanggal ini
        $existing = IzinCuti::where('pegawai_id', $request->pegawai_id)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Izin/Cuti/Sakit untuk tanggal ini sudah ada.')
                ->withInput();
        }

        IzinCuti::create([
            'pegawai_id' => $request->pegawai_id,
            'tanggal' => $request->tanggal,
            'jenis' => $request->jenis,
            'keterangan' => $request->keterangan,
            'status' => 'pending',
        ]);

        return redirect()->route('laporan.tidakMasuk', [
            'tanggal_mulai' => Carbon::parse($request->tanggal)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::parse($request->tanggal)->format('Y-m-d'),
        ])->with('success', 'Izin/Cuti/Sakit berhasil dibuat dan menunggu persetujuan.');
    }

    /**
     * Tampilkan form untuk edit izin/cuti
     */
    public function edit($id)
    {
        $izinCuti = IzinCuti::with('pegawai')->findOrFail($id);
        return view('admin.izin-cuti.edit', compact('izinCuti'));
    }

    /**
     * Update izin/cuti
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'jenis' => 'required|in:izin,cuti,sakit',
            'keterangan' => 'nullable|string',
        ]);

        $izinCuti = IzinCuti::findOrFail($id);
        $izinCuti->update([
            'jenis' => $request->jenis,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('laporan.tidakMasuk', [
            'tanggal_mulai' => $izinCuti->tanggal->format('Y-m-d'),
            'tanggal_selesai' => $izinCuti->tanggal->format('Y-m-d'),
        ])->with('success', 'Izin/Cuti/Sakit berhasil diupdate.');
    }

    /**
     * Setujui izin/cuti
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string',
        ]);

        $izinCuti = IzinCuti::findOrFail($id);
        
        if ($izinCuti->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Izin/Cuti/Sakit ini sudah diproses sebelumnya.');
        }

        $izinCuti->update([
            'status' => 'disetujui',
            'disetujui_oleh' => auth('web')->id(),
            'waktu_persetujuan' => now(),
            'keterangan' => $request->keterangan ?? $izinCuti->keterangan,
        ]);

        return redirect()->route('laporan.tidakMasuk', [
            'tanggal_mulai' => $izinCuti->tanggal->format('Y-m-d'),
            'tanggal_selesai' => $izinCuti->tanggal->format('Y-m-d'),
        ])->with('success', 'Izin/Cuti/Sakit berhasil disetujui.');
    }

    /**
     * Tolak izin/cuti
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string',
        ]);

        $izinCuti = IzinCuti::findOrFail($id);
        
        if ($izinCuti->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Izin/Cuti/Sakit ini sudah diproses sebelumnya.');
        }

        $izinCuti->update([
            'status' => 'ditolak',
            'disetujui_oleh' => auth('web')->id(),
            'waktu_persetujuan' => now(),
            'alasan_penolakan' => $request->alasan_penolakan,
        ]);

        return redirect()->route('laporan.tidakMasuk', [
            'tanggal_mulai' => $izinCuti->tanggal->format('Y-m-d'),
            'tanggal_selesai' => $izinCuti->tanggal->format('Y-m-d'),
        ])->with('success', 'Izin/Cuti/Sakit berhasil ditolak.');
    }

    /**
     * Hapus izin/cuti
     */
    public function destroy($id)
    {
        $izinCuti = IzinCuti::findOrFail($id);
        $tanggal = $izinCuti->tanggal;
        $izinCuti->delete();

        return redirect()->route('laporan.tidakMasuk', [
            'tanggal_mulai' => $tanggal->format('Y-m-d'),
            'tanggal_selesai' => $tanggal->format('Y-m-d'),
        ])->with('success', 'Izin/Cuti/Sakit berhasil dihapus.');
    }
}
