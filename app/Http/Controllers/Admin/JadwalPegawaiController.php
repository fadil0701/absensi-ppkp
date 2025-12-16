<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPegawai;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class JadwalPegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Query pegawai yang memiliki jadwal dengan eager load satpelkes dan jadwal
        $query = Pegawai::with(['satpelkes', 'jadwal'])
            ->whereHas('jadwal');

        // Filter berdasarkan pegawai (jika dipilih)
        if ($request->pegawai_id) {
            $query->where('id', $request->pegawai_id);
        }

        // Filter berdasarkan status aktif jadwal
        if ($request->has('is_aktif') && $request->is_aktif !== '') {
            $query->whereHas('jadwal', function($q) use ($request) {
                $q->where('is_aktif', $request->is_aktif == '1' ? 1 : 0);
            });
        }

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        // Get paginated results
        $pegawai = $query->orderBy('nama')->paginate(10);
        
        // Hitung total jadwal aktif untuk setiap pegawai
        // Pastikan kita menggunakan getCollection() untuk memodifikasi items
        $pegawai->getCollection()->transform(function ($p) {
            if (is_object($p) && isset($p->jadwal) && $p->jadwal instanceof \Illuminate\Database\Eloquent\Collection) {
                $p->total_jadwal = $p->jadwal->where('is_aktif', true)->count();
            } else {
                $p->total_jadwal = 0;
            }
            return $p;
        });
        
        $pegawaiList = Pegawai::aktif()->orderBy('nama')->get();

        return view('admin.jadwal-pegawai.index', compact('pegawai', 'pegawaiList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $pegawaiList = Pegawai::aktif()->orderBy('nama')->get();
        
        // Pre-select pegawai jika ada parameter
        $selectedPegawai = $request->pegawai_id ? Pegawai::find($request->pegawai_id) : null;

        return view('admin.jadwal-pegawai.create', compact('pegawaiList', 'selectedPegawai'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'hari' => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
            'toleransi_telat' => 'nullable|integer|min:0|max:60',
            'is_aktif' => 'boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $validated['is_aktif'] = $request->has('is_aktif') ? true : false;
        $validated['toleransi_telat'] = $validated['toleransi_telat'] ?? 15;

        JadwalPegawai::create($validated);

        return redirect()->route('jadwal-pegawai.index')
            ->with('success', 'Jadwal pegawai berhasil ditambahkan.');
    }

    /**
     * Store multiple jadwal at once
     */
    public function storeMultiple(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'jadwal' => 'required|array|min:1',
            'jadwal.*.hari' => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jadwal.*.jam_masuk' => 'required|date_format:H:i',
            'jadwal.*.jam_keluar' => 'required|date_format:H:i',
            'jadwal.*.toleransi_telat' => 'nullable|integer|min:0|max:60',
            'jadwal.*.tanggal_mulai' => 'nullable|date',
            'jadwal.*.tanggal_selesai' => 'nullable|date',
            'jadwal.*.is_aktif' => 'nullable|boolean',
        ]);

        $created = 0;
        $errors = [];

        foreach ($request->jadwal as $index => $jadwalData) {
            // Validasi jam keluar harus setelah jam masuk
            $jamMasuk = \Carbon\Carbon::createFromFormat('H:i', $jadwalData['jam_masuk']);
            $jamKeluar = \Carbon\Carbon::createFromFormat('H:i', $jadwalData['jam_keluar']);
            
            if ($jamKeluar->lte($jamMasuk)) {
                $errors[] = "Row " . ($index + 1) . ": Jam keluar harus setelah jam masuk";
                continue;
            }

            // Validasi tanggal selesai harus setelah tanggal mulai jika keduanya diisi
            if (!empty($jadwalData['tanggal_mulai']) && !empty($jadwalData['tanggal_selesai'])) {
                if ($jadwalData['tanggal_selesai'] < $jadwalData['tanggal_mulai']) {
                    $errors[] = "Row " . ($index + 1) . ": Tanggal selesai harus setelah tanggal mulai";
                    continue;
                }
            }

            try {
                JadwalPegawai::create([
                    'pegawai_id' => $request->pegawai_id,
                    'hari' => $jadwalData['hari'] ?? null,
                    'jam_masuk' => $jadwalData['jam_masuk'],
                    'jam_keluar' => $jadwalData['jam_keluar'],
                    'toleransi_telat' => $jadwalData['toleransi_telat'] ?? 15,
                    'is_aktif' => isset($jadwalData['is_aktif']) ? true : false,
                    'tanggal_mulai' => $jadwalData['tanggal_mulai'] ?? null,
                    'tanggal_selesai' => $jadwalData['tanggal_selesai'] ?? null,
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['jadwal' => implode(', ', $errors)])
                ->with('warning', "Berhasil membuat {$created} jadwal, namun ada beberapa error.");
        }

        return redirect()->route('jadwal-pegawai.index')
            ->with('success', "Berhasil membuat {$created} jadwal pegawai.");
    }

    /**
     * Display the specified resource.
     * Menampilkan semua jadwal untuk satu pegawai
     */
    public function show($pegawai_id)
    {
        $pegawai = Pegawai::with(['jadwal' => function($q) {
            $q->orderBy('hari')->orderBy('jam_masuk');
        }])->findOrFail($pegawai_id);
        
        return view('admin.jadwal-pegawai.show', compact('pegawai'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $jadwal = JadwalPegawai::findOrFail($id);
        $pegawaiList = Pegawai::aktif()->orderBy('nama')->get();

        return view('admin.jadwal-pegawai.edit', compact('jadwal', 'pegawaiList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $jadwal = JadwalPegawai::findOrFail($id);

        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'hari' => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
            'toleransi_telat' => 'nullable|integer|min:0|max:60',
            'is_aktif' => 'boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $validated['is_aktif'] = $request->has('is_aktif') ? true : false;
        $validated['toleransi_telat'] = $validated['toleransi_telat'] ?? 15;

        $jadwal->update($validated);

        return redirect()->route('jadwal-pegawai.index')
            ->with('success', 'Jadwal pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jadwal = JadwalPegawai::findOrFail($id);
        $jadwal->delete();

        return redirect()->route('jadwal-pegawai.index')
            ->with('success', 'Jadwal pegawai berhasil dihapus.');
    }

    /**
     * Bulk create jadwal untuk satu pegawai (Senin-Jumat)
     */
    public function createBulk(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i|after:jam_masuk',
            'toleransi_telat' => 'nullable|integer|min:0|max:60',
            'hari' => 'required|array',
            'hari.*' => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $toleransiTelat = $validated['toleransi_telat'] ?? 15;
        $created = 0;

        foreach ($validated['hari'] as $hari) {
            // Cek apakah jadwal sudah ada
            $existing = JadwalPegawai::where('pegawai_id', $validated['pegawai_id'])
                ->where('hari', $hari)
                ->where(function($q) use ($validated) {
                    if ($validated['tanggal_mulai']) {
                        $q->whereNull('tanggal_mulai')
                          ->orWhere('tanggal_mulai', '<=', $validated['tanggal_mulai']);
                    }
                    if ($validated['tanggal_selesai']) {
                        $q->whereNull('tanggal_selesai')
                          ->orWhere('tanggal_selesai', '>=', $validated['tanggal_selesai']);
                    }
                })
                ->first();

            if (!$existing) {
                JadwalPegawai::create([
                    'pegawai_id' => $validated['pegawai_id'],
                    'hari' => $hari,
                    'jam_masuk' => $validated['jam_masuk'],
                    'jam_keluar' => $validated['jam_keluar'],
                    'toleransi_telat' => $toleransiTelat,
                    'is_aktif' => true,
                    'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
                    'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                ]);
                $created++;
            }
        }

        return redirect()->route('jadwal-pegawai.index')
            ->with('success', "Berhasil membuat {$created} jadwal untuk pegawai.");
    }
}
