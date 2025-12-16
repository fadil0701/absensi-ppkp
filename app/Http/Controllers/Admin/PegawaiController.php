<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Satpelkes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pegawai::with('satpelkes');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $pegawai = $query->latest()->paginate(10);

        return view('admin.pegawai.index', compact('pegawai'));
    }

    public function create()
    {
        $satpelkes = Satpelkes::where('is_aktif', true)->get();
        return view('admin.pegawai.create', compact('satpelkes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:pegawai,nip',
            'nama' => 'required',
            'email' => 'required|email|unique:pegawai,email',
            'password' => 'required|min:6',
            'divisi' => 'nullable|in:Struktural,Jabatan Pelaksana,Jabatan Fungsional',
            'jabatan' => 'nullable',
            'satpelkes_id' => 'nullable|exists:satpelkes,id',
            'role' => 'required|in:pegawai,pimpinan,admin',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        Pegawai::create([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'divisi' => $request->divisi,
            'jabatan' => $request->jabatan,
            'satpelkes_id' => $request->satpelkes_id,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function show(Pegawai $pegawai)
    {
        $pegawai->load(['satpelkes', 'presensi' => function($q) {
            $q->latest('waktu_absen')->limit(10);
        }]);
        return view('admin.pegawai.show', compact('pegawai'));
    }

    public function edit(Pegawai $pegawai)
    {
        $satpelkes = Satpelkes::where('is_aktif', true)->get();
        return view('admin.pegawai.edit', compact('pegawai', 'satpelkes'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $request->validate([
            'nip' => 'required|unique:pegawai,nip,' . $pegawai->id,
            'nama' => 'required',
            'email' => 'required|email|unique:pegawai,email,' . $pegawai->id,
            'password' => 'nullable|min:6',
            'divisi' => 'nullable|in:Struktural,Jabatan Pelaksana,Jabatan Fungsional',
            'jabatan' => 'nullable',
            'satpelkes_id' => 'nullable|exists:satpelkes,id',
            'role' => 'required|in:pegawai,pimpinan,admin',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $data = $request->except('password');
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pegawai->update($data);

        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil diupdate.');
    }

    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();
        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}

