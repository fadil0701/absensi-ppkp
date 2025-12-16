<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TugasLuar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TugasLuarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TugasLuar::with(['pegawai', 'disetujuiOleh']);

        // Filter berdasarkan role
        if (auth('web')->user()->role === 'pegawai') {
            $query->where('pegawai_id', auth('web')->id());
        }

        // Search
        if ($request->search) {
            $query->whereHas('pegawai', function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        // Filter status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter tanggal
        if ($request->tanggal_mulai) {
            $query->where('tanggal_selesai', '>=', $request->tanggal_mulai);
        }

        if ($request->tanggal_selesai) {
            $query->where('tanggal_mulai', '<=', $request->tanggal_selesai);
        }

        $tugasLuar = $query->latest('created_at')->paginate(10);

        return view('admin.tugas-luar.index', compact('tugasLuar'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pegawaiList = \App\Models\Pegawai::aktif()->orderBy('nama')->get();
        
        return view('admin.tugas-luar.create', compact('pegawaiList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi_tugas' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:500',
            'dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB
        ]);

        // Jika pegawai biasa, hanya bisa buat untuk dirinya sendiri
        if (auth('web')->user()->role === 'pegawai') {
            $validated['pegawai_id'] = auth('web')->id();
        }

        // Upload dokumen jika ada
        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');
            $filename = 'tugas_luar_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('tugas_luar/dokumen', $filename, 'public');
            $validated['dokumen'] = $path;
        }

        $validated['status'] = 'pending';

        TugasLuar::create($validated);

        return redirect()->route('tugas-luar.index')
            ->with('success', 'Tugas luar berhasil diajukan. Menunggu persetujuan pimpinan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tugasLuar = TugasLuar::with(['pegawai', 'disetujuiOleh'])->findOrFail($id);
        
        // Check authorization
        if (auth('web')->user()->role === 'pegawai' && $tugasLuar->pegawai_id !== auth('web')->id()) {
            abort(403);
        }

        return view('admin.tugas-luar.show', compact('tugasLuar'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tugasLuar = TugasLuar::findOrFail($id);
        
        // Check authorization
        if (auth('web')->user()->role === 'pegawai' && $tugasLuar->pegawai_id !== auth('web')->id()) {
            abort(403);
        }

        // Hanya bisa edit jika status pending
        if ($tugasLuar->status !== 'pending') {
            return redirect()->route('tugas-luar.show', $tugasLuar)
                ->with('error', 'Tugas luar yang sudah disetujui/ditolak tidak dapat diubah.');
        }

        $pegawaiList = \App\Models\Pegawai::aktif()->orderBy('nama')->get();

        return view('admin.tugas-luar.edit', compact('tugasLuar', 'pegawaiList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tugasLuar = TugasLuar::findOrFail($id);
        
        // Check authorization
        if (auth('web')->user()->role === 'pegawai' && $tugasLuar->pegawai_id !== auth('web')->id()) {
            abort(403);
        }

        // Hanya bisa edit jika status pending
        if ($tugasLuar->status !== 'pending') {
            return redirect()->route('tugas-luar.show', $tugasLuar)
                ->with('error', 'Tugas luar yang sudah disetujui/ditolak tidak dapat diubah.');
        }

        $validated = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi_tugas' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:500',
            'dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB
            'hapus_dokumen' => 'nullable|boolean',
        ]);

        // Jika pegawai biasa, tidak bisa ubah pegawai_id
        if (auth('web')->user()->role === 'pegawai') {
            $validated['pegawai_id'] = $tugasLuar->pegawai_id;
        } else {
            $validated['pegawai_id'] = $request->pegawai_id ?? $tugasLuar->pegawai_id;
        }

        // Handle dokumen: hapus dokumen lama jika diminta atau jika upload dokumen baru
        if ($request->has('hapus_dokumen') && $request->hapus_dokumen) {
            if ($tugasLuar->dokumen && Storage::disk('public')->exists($tugasLuar->dokumen)) {
                Storage::disk('public')->delete($tugasLuar->dokumen);
            }
            $validated['dokumen'] = null;
        } elseif ($request->hasFile('dokumen')) {
            // Hapus dokumen lama jika ada
            if ($tugasLuar->dokumen && Storage::disk('public')->exists($tugasLuar->dokumen)) {
                Storage::disk('public')->delete($tugasLuar->dokumen);
            }
            // Upload dokumen baru
            $file = $request->file('dokumen');
            $filename = 'tugas_luar_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('tugas_luar/dokumen', $filename, 'public');
            $validated['dokumen'] = $path;
        } else {
            // Pertahankan dokumen lama
            unset($validated['dokumen']);
        }

        $tugasLuar->update($validated);

        return redirect()->route('tugas-luar.show', $tugasLuar)
            ->with('success', 'Tugas luar berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tugasLuar = TugasLuar::findOrFail($id);
        
        // Check authorization
        if (auth('web')->user()->role === 'pegawai' && $tugasLuar->pegawai_id !== auth('web')->id()) {
            abort(403);
        }

        // Hanya bisa hapus jika status pending
        if ($tugasLuar->status !== 'pending') {
            return redirect()->route('tugas-luar.show', $tugasLuar)
                ->with('error', 'Tugas luar yang sudah disetujui/ditolak tidak dapat dihapus.');
        }

        // Hapus file dokumen terkait jika ada
        if ($tugasLuar->dokumen && Storage::disk('public')->exists($tugasLuar->dokumen)) {
            Storage::disk('public')->delete($tugasLuar->dokumen);
        }

        $tugasLuar->delete();

        return redirect()->route('tugas-luar.index')
            ->with('success', 'Tugas luar berhasil dihapus.');
    }

    /**
     * Approve tugas luar (untuk pimpinan/admin)
     */
    public function approve(Request $request, $id)
    {
        $tugasLuar = TugasLuar::findOrFail($id);

        $tugasLuar->update([
            'status' => 'disetujui',
            'disetujui_oleh' => auth('web')->id(),
            'waktu_persetujuan' => now(),
        ]);

        return redirect()->route('tugas-luar.index')
            ->with('success', 'Tugas luar berhasil disetujui.');
    }

    /**
     * Reject tugas luar (untuk pimpinan/admin)
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:500',
        ]);

        $tugasLuar = TugasLuar::findOrFail($id);

        $tugasLuar->update([
            'status' => 'ditolak',
            'disetujui_oleh' => auth('web')->id(),
            'waktu_persetujuan' => now(),
            'keterangan' => $request->keterangan ?? $tugasLuar->keterangan,
        ]);

        return redirect()->route('tugas-luar.index')
            ->with('success', 'Tugas luar ditolak.');
    }

    /**
     * Pending tugas luar (untuk pimpinan/admin - review)
     */
    public function pending()
    {
        $tugasLuar = TugasLuar::where('status', 'pending')
            ->with(['pegawai'])
            ->latest('created_at')
            ->paginate(10);

        return view('admin.tugas-luar.pending', compact('tugasLuar'));
    }
}

