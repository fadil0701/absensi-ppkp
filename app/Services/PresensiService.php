<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Satpelkes;
use App\Models\Pegawai;
use App\Models\JadwalPegawai;
use App\Models\TugasLuar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PresensiService
{
    protected GPSService $gpsService;
    protected WatermarkService $watermarkService;

    public function __construct(GPSService $gpsService, WatermarkService $watermarkService)
    {
        $this->gpsService = $gpsService;
        $this->watermarkService = $watermarkService;
    }

    /**
     * Proses check-in atau check-out
     */
    public function processCheckIn(
        int $pegawaiId,
        string $jenis,
        float $latitude,
        float $longitude,
        float $accuracy,
        string $deviceId,
        string $fotoBase64,
        ?string $keterangan = null,
        bool $isTugasLuarManual = false
    ): array {
        // Cek apakah pegawai sedang tugas luar (dari database) atau memilih tugas luar manual
        $tugasLuar = TugasLuar::where('pegawai_id', $pegawaiId)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', today())
            ->where('tanggal_selesai', '>=', today())
            ->first();
        
        // Jika sedang tugas luar atau memilih tugas luar manual, skip validasi accuracy GPS
        if ($tugasLuar || $isTugasLuarManual) {
            // Untuk tugas luar: hanya validasi koordinat (tidak perlu validasi accuracy)
            $coordinateValidation = $this->gpsService->validateCoordinates($latitude, $longitude);
            if (!$coordinateValidation) {
                throw new \Exception('Koordinat GPS tidak valid');
            }
            
            // Absen tugas luar - status PENDING, perlu approval pimpinan
            $keteranganFinal = $keterangan ?? ($tugasLuar ? 'Tugas Luar: ' . $tugasLuar->lokasi_tugas : 'Tugas Luar Manual');
            
            return $this->processTugasLuarCheckIn(
                $pegawaiId,
                $jenis,
                $latitude,
                $longitude,
                $accuracy,
                $deviceId,
                $fotoBase64,
                $keteranganFinal,
                $tugasLuar
            );
        }

        // Untuk absensi rutin: dapatkan satpelkes terdekat terlebih dahulu untuk mendapatkan radius
        $satpelkesTerdekat = $this->getSatpelkesTerdekat($latitude, $longitude);
        
        // Gunakan radius_absensi dari satpelkes terdekat sebagai maxAccuracy (atau 100 jika tidak ada)
        $maxAccuracy = $satpelkesTerdekat ? $satpelkesTerdekat['radius'] : 100;
        
        // Validasi GPS lengkap termasuk accuracy dengan menggunakan radius dari satpelkes
        $gpsValidation = $this->gpsService->validateGPS($latitude, $longitude, $accuracy, $maxAccuracy);
        if (!$gpsValidation['valid']) {
            throw new \Exception(implode(', ', $gpsValidation['errors']));
        }

        // ABSENSI RUTIN - Langsung APPROVED (tanpa perlu approval, tidak peduli zona)
        // Cek velocity
        $velocityCheck = $this->gpsService->checkVelocity($pegawaiId, $latitude, $longitude);
        if ($velocityCheck['suspicious']) {
            Log::warning('Suspicious velocity detected', [
                'pegawai_id' => $pegawaiId,
                'velocity' => $velocityCheck['velocity'],
            ]);
        }

        // Panggil stored procedure untuk proses check-in (untuk mendapatkan presensi_id dan data satpelkes)
        // Status dari stored procedure akan diabaikan dan langsung di-set menjadi APPROVED
        DB::statement('CALL sp_process_checkin(?, ?, ?, ?, ?, ?, ?, ?, ?, @p_presensi_id, @p_status, @p_satpelkes_id, @p_jarak)', [
            $pegawaiId,
            $jenis,
            $latitude,
            $longitude,
            $accuracy,
            $deviceId,
            null, // foto_asli akan diupdate setelah watermark
            request()->ip(),
            request()->userAgent(),
        ]);

        // Ambil output parameters
        $output = DB::select('SELECT @p_presensi_id as presensi_id, @p_status as status, @p_satpelkes_id as satpelkes_id, @p_jarak as jarak');

        $presensiId = $output[0]->presensi_id;
        $satpelkesId = $output[0]->satpelkes_id;
        $jarak = $output[0]->jarak;
        
        // ABSENSI RUTIN: Langsung set status APPROVED (abaikan status dari stored procedure)
        // Status akan langsung APPROVED meskipun OUT_ZONE, karena ini absensi rutin yang tidak perlu approval
        $status = 'APPROVED';
        
        // Update status presensi menjadi APPROVED dengan force
        Presensi::where('id', $presensiId)->update([
            'status' => 'APPROVED',
            'keterangan' => $keterangan, // Simpan keterangan jika ada
        ]);

        // Proses foto dan watermark
        $fotoData = $this->watermarkService->processFoto(
            $fotoBase64,
            $presensiId,
            $pegawaiId,
            $latitude,
            $longitude,
            $accuracy,
            $satpelkesId,
            $jarak,
            $status
        );

        // Update presensi dengan path foto, keterangan, dan PASTIKAN status tetap APPROVED
        $presensi = Presensi::find($presensiId);
        $updateData = [
            'foto_asli' => $fotoData['foto_asli'],
            'foto_watermark' => $fotoData['foto_watermark'],
            'status' => 'APPROVED', // Pastikan status tetap APPROVED untuk absensi rutin
        ];
        
        if ($keterangan) {
            $updateData['keterangan'] = $keterangan;
        }
        
        $presensi->update($updateData);

        return [
            'presensi_id' => $presensiId,
            'status' => $status,
            'satpelkes_id' => $satpelkesId,
            'jarak' => $jarak,
            'foto_watermark' => $fotoData['foto_watermark'],
        ];
    }

    /**
     * Proses check-in untuk pegawai yang sedang tugas luar
     * Status PENDING - perlu approval pimpinan
     */
    protected function processTugasLuarCheckIn(
        int $pegawaiId,
        string $jenis,
        float $latitude,
        float $longitude,
        float $accuracy,
        string $deviceId,
        string $fotoBase64,
        ?string $keterangan,
        ?TugasLuar $tugasLuar = null
    ): array {
        // Cari satpelkes terdekat untuk referensi (opsional)
        $satpelkesTerdekat = $this->getSatpelkesTerdekat($latitude, $longitude);
        $satpelkesId = $satpelkesTerdekat ? $satpelkesTerdekat['id'] : null;
        $jarak = $satpelkesTerdekat ? $satpelkesTerdekat['jarak'] : null;

        // Buat presensi dengan status PENDING (karena tugas luar perlu approval)
        $presensi = Presensi::create([
            'pegawai_id' => $pegawaiId,
            'tanggal' => today(),
            'jenis' => $jenis,
            'waktu_absen' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'device_id' => $deviceId,
            'satpelkes_id' => $satpelkesId,
            'jarak_ke_satpelkes' => $jarak,
            'status' => 'OUT_ZONE_PENDING', // Tugas luar perlu approval pimpinan
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'keterangan' => $keterangan ?? ($tugasLuar ? 'Tugas Luar: ' . $tugasLuar->lokasi_tugas : 'Tugas Luar Manual'),
        ]);

        // Proses foto dan watermark
        $fotoData = $this->watermarkService->processFoto(
            $fotoBase64,
            $presensi->id,
            $pegawaiId,
            $latitude,
            $longitude,
            $accuracy,
            $satpelkesId,
            $jarak,
            'OUT_ZONE_PENDING'
        );

        // Update presensi dengan path foto
        $presensi->update([
            'foto_asli' => $fotoData['foto_asli'],
            'foto_watermark' => $fotoData['foto_watermark'],
        ]);

        return [
            'presensi_id' => $presensi->id,
            'status' => 'OUT_ZONE_PENDING',
            'satpelkes_id' => $satpelkesId,
            'jarak' => $jarak,
            'foto_watermark' => $fotoData['foto_watermark'],
            'tugas_luar' => true,
        ];
    }

    /**
     * Hitung telat berdasarkan jadwal
     */
    public function hitungTelat(Presensi $presensi): array
    {
        $jadwal = JadwalPegawai::where('pegawai_id', $presensi->pegawai_id)
            ->aktif()
            ->untukTanggal($presensi->tanggal)
            ->untukHari($presensi->tanggal)
            ->first();

        if (!$jadwal) {
            return [
                'telat' => false,
                'menit_telat' => 0,
            ];
        }

        $jamMasuk = Carbon::parse($jadwal->jam_masuk);
        $jamCheckIn = Carbon::parse($presensi->waktu_absen);
        $batasWaktu = $jamMasuk->copy()->addMinutes($jadwal->toleransi_telat);

        if ($jamCheckIn->greaterThan($batasWaktu)) {
            $menitTelat = (int) $jamCheckIn->diffInMinutes($batasWaktu);

            return [
                'telat' => true,
                'menit_telat' => $menitTelat,
            ];
        }

        return [
            'telat' => false,
            'menit_telat' => 0,
        ];
    }

    /**
     * Ambil satpelkes terdekat
     */
    public function getSatpelkesTerdekat(float $latitude, float $longitude): ?array
    {
        $satpelkesList = Satpelkes::aktif()->get();
        $satpelkesTerdekat = null;
        $jarakTerdekat = PHP_INT_MAX;

        foreach ($satpelkesList as $satpelkes) {
            $jarak = $this->gpsService->haversineDistance(
                $latitude,
                $longitude,
                $satpelkes->latitude,
                $satpelkes->longitude
            );

            if ($jarak < $jarakTerdekat) {
                $jarakTerdekat = $jarak;
                $satpelkesTerdekat = [
                    'id' => $satpelkes->id,
                    'nama' => $satpelkes->nama_satpelkes,
                    'kode' => $satpelkes->kode_satpelkes,
                    'jarak' => $jarak,
                    'radius' => $satpelkes->radius_absensi,
                ];
            }
        }

        return $satpelkesTerdekat;
    }
}
