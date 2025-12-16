<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Satpelkes;
use Illuminate\Http\Request;

class SatpelkesController extends Controller
{
    public function index(Request $request)
    {
        $query = Satpelkes::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_satpelkes', 'like', "%{$request->search}%")
                  ->orWhere('kode_satpelkes', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('is_aktif')) {
            $query->where('is_aktif', $request->is_aktif);
        }

        $satpelkes = $query->latest()->paginate(10);

        return view('admin.satpelkes.index', compact('satpelkes'));
    }

    public function create()
    {
        return view('admin.satpelkes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_satpelkes' => 'required',
            'kode_satpelkes' => 'required|unique:satpelkes,kode_satpelkes',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_absensi' => 'required|integer|min:10',
            'alamat' => 'nullable',
            'is_aktif' => 'required|boolean',
        ]);

        Satpelkes::create($request->all());

        return redirect()->route('satpelkes.index')->with('success', 'Satpelkes berhasil ditambahkan.');
    }

    public function show(Satpelkes $satpelke)
    {
        $satpelke->load('pegawai');
        return view('admin.satpelkes.show', compact('satpelke'));
    }

    public function edit(Satpelkes $satpelke)
    {
        return view('admin.satpelkes.edit', compact('satpelke'));
    }

    public function update(Request $request, Satpelkes $satpelke)
    {
        $request->validate([
            'nama_satpelkes' => 'required',
            'kode_satpelkes' => 'required|unique:satpelkes,kode_satpelkes,' . $satpelke->id,
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_absensi' => 'required|integer|min:10',
            'alamat' => 'nullable',
            'is_aktif' => 'required|boolean',
        ]);

        $satpelke->update($request->all());

        return redirect()->route('satpelkes.index')->with('success', 'Satpelkes berhasil diupdate.');
    }

    public function destroy(Satpelkes $satpelke)
    {
        $satpelke->delete();
        return redirect()->route('satpelkes.index')->with('success', 'Satpelkes berhasil dihapus.');
    }
}

