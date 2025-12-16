<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PresensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{
    protected PresensiService $presensiService;

    public function __construct(PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    /**
     * Show absensi form
     */
    public function index()
    {
        $pegawai = auth('web')->user();
        
        // Cek apakah sudah check-in hari ini
        $checkInToday = \App\Models\Presensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', today())
            ->where('jenis', 'check_in')
            ->first();
            
        $checkOutToday = \App\Models\Presensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', today())
            ->where('jenis', 'check_out')
            ->first();

        // Cek apakah sedang tugas luar hari ini (disetujui)
        $tugasLuar = \App\Models\TugasLuar::where('pegawai_id', $pegawai->id)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', today())
            ->where('tanggal_selesai', '>=', today())
            ->first();
            
        // Cek apakah ada tugas luar pending (belum disetujui)
        $tugasLuarPending = \App\Models\TugasLuar::where('pegawai_id', $pegawai->id)
            ->where('status', 'pending')
            ->where('tanggal_mulai', '<=', today())
            ->where('tanggal_selesai', '>=', today())
            ->first();

        // Get satpelkes terdekat (jika ada)
        $satpelkes = \App\Models\Satpelkes::where('is_aktif', true)->get();

        return view('admin.absensi.index', compact('checkInToday', 'checkOutToday', 'satpelkes', 'tugasLuar', 'tugasLuarPending'));
    }

    /**
     * Process check-in
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0|max:500',
            'device_id' => 'required|string',
            'foto' => 'required|string',
            'keterangan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessage = 'Validasi gagal: ';
            
            if ($errors->has('foto')) {
                $errorMessage .= 'Foto WAJIB diambil untuk absensi (termasuk tugas luar). ';
            }
            if ($errors->has('latitude') || $errors->has('longitude')) {
                $errorMessage .= 'Lokasi GPS wajib didapatkan. ';
            }
            if ($errors->has('device_id')) {
                $errorMessage .= 'Device ID wajib. ';
            }
            
            $errorMessage .= 'Pastikan semua field diisi dengan benar.';
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $errorMessage);
        }

        try {
            $pegawai = auth('web')->user();

            // Cek apakah sudah check-in hari ini
            $existingCheckIn = \App\Models\Presensi::where('pegawai_id', $pegawai->id)
                ->where('tanggal', today())
                ->where('jenis', 'check_in')
                ->first();

            if ($existingCheckIn) {
                return redirect()->back()->with('error', 'Anda sudah melakukan check-in hari ini.');
            }

            // Validasi: Jika ada tugas luar pending dan bukan memilih tugas luar manual, tidak boleh absen
            if ($request->jenis_absensi !== 'tugas_luar') {
                $tugasLuarPending = \App\Models\TugasLuar::where('pegawai_id', $pegawai->id)
                    ->where('status', 'pending')
                    ->where('tanggal_mulai', '<=', today())
                    ->where('tanggal_selesai', '>=', today())
                    ->first();
                    
                if ($tugasLuarPending) {
                    return redirect()->back()
                        ->with('error', 'Anda memiliki tugas luar yang masih pending. Silakan tunggu persetujuan pimpinan terlebih dahulu.')
                        ->withInput();
                }
            }

            $result = $this->presensiService->processCheckIn(
                $pegawai->id,
                'check_in',
                $request->latitude,
                $request->longitude,
                $request->accuracy,
                $request->device_id,
                $request->foto,
                $request->keterangan,
                $request->jenis_absensi === 'tugas_luar'
            );

            // Tentukan message berdasarkan status dan jenis absensi
            if (isset($result['tugas_luar']) && $result['tugas_luar']) {
                // Absen tugas luar - perlu approval pimpinan
                $message = 'Check-in tugas luar berhasil! Status: PENDING. Menunggu approval pimpinan.';
            } else {
                // Absen rutin - langsung approved
                $message = 'Check-in berhasil! Status: APPROVED.';
            }

            return redirect()->route('absensi.index')
                ->with('success', $message)
                ->with('presensi_data', $result);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Cek apakah error terkait dengan foto/decode
            if (stripos($errorMessage, 'decode') !== false || 
                stripos($errorMessage, 'gambar') !== false ||
                stripos($errorMessage, 'foto') !== false) {
                $errorMessage = 'Error memproses foto: ' . $errorMessage . ' Silakan ambil foto ulang.';
            } else {
                $errorMessage = 'Error: ' . $errorMessage;
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Process check-out
     */
    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0|max:500',
            'device_id' => 'required|string',
            'foto' => 'required|string',
            'jenis_absensi' => 'required|in:rutin,tugas_luar',
            'keterangan' => 'nullable|string|max:500',
        ]);
        
        // Validasi: jika tugas luar, keterangan wajib
        if ($request->jenis_absensi === 'tugas_luar') {
            $validator->sometimes('keterangan', 'required|string|max:500', function ($input) {
                return true;
            });
        }

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessage = 'Validasi gagal: ';
            
            if ($errors->has('foto')) {
                $errorMessage .= 'Foto WAJIB diambil untuk absensi (termasuk tugas luar). ';
            }
            if ($errors->has('latitude') || $errors->has('longitude')) {
                $errorMessage .= 'Lokasi GPS wajib didapatkan. ';
            }
            if ($errors->has('device_id')) {
                $errorMessage .= 'Device ID wajib. ';
            }
            
            $errorMessage .= 'Pastikan semua field diisi dengan benar.';
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $errorMessage);
        }

        try {
            $pegawai = auth('web')->user();

            // Cek apakah sudah check-out hari ini
            $existingCheckOut = \App\Models\Presensi::where('pegawai_id', $pegawai->id)
                ->where('tanggal', today())
                ->where('jenis', 'check_out')
                ->first();

            if ($existingCheckOut) {
                return redirect()->back()->with('error', 'Anda sudah melakukan check-out hari ini.');
            }

            $result = $this->presensiService->processCheckIn(
                $pegawai->id,
                'check_out',
                $request->latitude,
                $request->longitude,
                $request->accuracy,
                $request->device_id,
                $request->foto,
                $request->keterangan,
                $request->jenis_absensi === 'tugas_luar'
            );

            // Tentukan message berdasarkan status dan jenis absensi
            if (isset($result['tugas_luar']) && $result['tugas_luar']) {
                // Absen tugas luar - perlu approval pimpinan
                $message = 'Check-out tugas luar berhasil! Status: PENDING. Menunggu approval pimpinan.';
            } else {
                // Absen rutin - langsung approved
                $message = 'Check-out berhasil! Status: APPROVED.';
            }

            return redirect()->route('absensi.index')
                ->with('success', $message)
                ->with('presensi_data', $result);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Cek apakah error terkait dengan foto/decode
            if (stripos($errorMessage, 'decode') !== false || 
                stripos($errorMessage, 'gambar') !== false ||
                stripos($errorMessage, 'foto') !== false) {
                $errorMessage = 'Error memproses foto: ' . $errorMessage . ' Silakan ambil foto ulang.';
            } else {
                $errorMessage = 'Error: ' . $errorMessage;
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }
}

