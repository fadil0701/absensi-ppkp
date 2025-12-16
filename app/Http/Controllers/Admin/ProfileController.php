<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile
     */
    public function show()
    {
        $pegawai = auth('web')->user();
        $pegawai->load('satpelkes');
        
        return view('admin.profile.show', compact('pegawai'));
    }

    /**
     * Show the form for editing the user's profile
     */
    public function edit()
    {
        $pegawai = auth('web')->user();
        $pegawai = Pegawai::with('satpelkes')->find($pegawai->id);
        
        // Get list of satpelkes for dropdown (only if admin)
        $satpelkesList = null;
        if (auth('web')->user()->role === 'admin') {
            $satpelkesList = \App\Models\Satpelkes::where('is_aktif', true)->orderBy('nama_satpelkes')->get();
        }
        
        return view('admin.profile.edit', compact('pegawai', 'satpelkesList'));
    }

    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $pegawai = auth('web')->user();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pegawai,email,' . $pegawai->id,
            'divisi' => 'nullable|in:Struktural,Jabatan Pelaksana,Jabatan Fungsional',
            'jabatan' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'satpelkes_id' => auth('web')->user()->role === 'admin' ? 'nullable|exists:satpelkes,id' : 'nullable',
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old foto if exists
            if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
                Storage::disk('public')->delete($pegawai->foto);
            }
            
            $file = $request->file('foto');
            $filename = 'pegawai_' . $pegawai->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pegawai/foto', $filename, 'public');
            $validated['foto'] = $path;
        }

        // Only admin can change satpelkes_id
        if (auth('web')->user()->role !== 'admin') {
            unset($validated['satpelkes_id']);
        }

        $pegawai->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profile berhasil diperbarui.');
    }
}
