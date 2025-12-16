<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GPSService
{
    /**
     * Hitung jarak antara dua koordinat GPS menggunakan Haversine formula
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $result = DB::selectOne(
            "SELECT haversine_distance(?, ?, ?, ?) as distance",
            [$lat1, $lng1, $lat2, $lng2]
        );

        return (float) $result->distance;
    }

    /**
     * Validasi GPS accuracy
     * Accuracy lebih kecil = lebih baik (lebih akurat)
     * Jika accuracy > threshold = kurang akurat = ditolak
     */
    public function validateAccuracy(float $accuracy, float $maxAccuracy = 100): bool
    {
        if ($accuracy > $maxAccuracy) {
            return false;
        }

        return true;
    }

    /**
     * Validasi koordinat GPS
     */
    public function validateCoordinates(float $latitude, float $longitude): bool
    {
        // Validasi latitude (-90 sampai 90)
        if ($latitude < -90 || $latitude > 90) {
            return false;
        }

        // Validasi longitude (-180 sampai 180)
        if ($longitude < -180 || $longitude > 180) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah GPS coordinate valid
     */
    public function validateGPS(float $latitude, float $longitude, float $accuracy, float $maxAccuracy = 100): array
    {
        $errors = [];

        // Validasi koordinat
        if (!$this->validateCoordinates($latitude, $longitude)) {
            $errors[] = 'Koordinat GPS tidak valid';
        }

        // Validasi accuracy
        // Accuracy lebih kecil = lebih baik (lebih akurat)
        // Jika accuracy > threshold = kurang akurat = ditolak
        if (!$this->validateAccuracy($accuracy, $maxAccuracy)) {
            $errors[] = "GPS accuracy terlalu rendah (maksimal {$maxAccuracy} meter, saat ini: " . number_format($accuracy, 2) . " meter)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Cek velocity (kecepatan perpindahan) untuk deteksi gerakan tidak mungkin
     */
    public function checkVelocity(int $pegawaiId, float $latitude, float $longitude): array
    {
        $lastPresensi = \App\Models\Presensi::where('pegawai_id', $pegawaiId)
            ->latest('waktu_absen')
            ->first();

        if (!$lastPresensi) {
            return [
                'suspicious' => false,
                'velocity' => 0,
            ];
        }

        $jarak = $this->haversineDistance(
            $lastPresensi->latitude,
            $lastPresensi->longitude,
            $latitude,
            $longitude
        );

        $waktuSelisih = $lastPresensi->waktu_absen->diffInSeconds(now());

        if ($waktuSelisih > 0) {
            $kecepatan = ($jarak / $waktuSelisih) * 3.6; // km/jam

            if ($kecepatan > 200) {
                Log::warning('Gerakan tidak mungkin terdeteksi', [
                    'pegawai_id' => $pegawaiId,
                    'jarak' => $jarak,
                    'waktu_selisih' => $waktuSelisih,
                    'kecepatan' => $kecepatan,
                ]);

                return [
                    'suspicious' => true,
                    'velocity' => $kecepatan,
                    'jarak' => $jarak,
                    'waktu_selisih' => $waktuSelisih,
                ];
            }
        }

        return [
            'suspicious' => false,
            'velocity' => 0,
        ];
    }
}

