<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Pegawai;
use App\Models\TugasLuar;
use App\Models\IzinCuti;
use App\Models\JadwalPegawai;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanService
{
    /**
     * Get laporan telat
     */
    public function getLaporanTelat(string $tanggalMulai, string $tanggalSelesai, ?int $pegawaiId = null): array
    {
        // Ambil semua presensi check_in dalam periode
        $query = Presensi::with(['pegawai', 'satpelkes'])
            ->where('jenis', 'check_in')
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->where('status', '!=', 'REJECTED')
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_absen', 'desc');

        if ($pegawaiId) {
            $query->where('pegawai_id', $pegawaiId);
        }

        $presensiList = $query->get();
        $data = [];

        foreach ($presensiList as $presensi) {
            // Ambil jadwal untuk tanggal tersebut
            $jadwal = \App\Models\JadwalPegawai::where('pegawai_id', $presensi->pegawai_id)
                ->aktif()
                ->untukTanggal($presensi->tanggal)
                ->untukHari($presensi->tanggal)
                ->first();

            if ($jadwal) {
                $jamMasukJadwal = Carbon::parse($jadwal->jam_masuk);
                $waktuCheckIn = Carbon::parse($presensi->waktu_absen);
                $batasWaktu = $jamMasukJadwal->copy()->addMinutes($jadwal->toleransi_telat);

                // Jika check-in lebih dari batas waktu (termasuk toleransi), maka telat
                if ($waktuCheckIn->greaterThan($batasWaktu)) {
                    $menitTelat = (int) $waktuCheckIn->diffInMinutes($batasWaktu);

                    $data[] = [
                        'nip' => $presensi->pegawai->nip ?? '-',
                        'nama' => $presensi->pegawai->nama ?? '-',
                        'divisi' => $presensi->pegawai->divisi ?? '-',
                        'jabatan' => $presensi->pegawai->jabatan ?? '-',
                        'satpelkes_nama' => $presensi->satpelkes->nama_satpelkes ?? '-',
                        'tanggal' => $presensi->tanggal->format('Y-m-d'),
                        'jam_masuk' => $jadwal->jam_masuk,
                        'jam_checkin' => $presensi->waktu_absen->format('H:i:s'),
                        'menit_telat' => $menitTelat,
                        'status_kehadiran' => $presensi->status,
                        'foto_watermark' => $presensi->foto_watermark ?? null,
                    ];
                }
            }
        }

        // Sort by menit_telat descending
        usort($data, function ($a, $b) {
            return $b['menit_telat'] <=> $a['menit_telat'];
        });

        return $data;
    }

    /**
     * Get laporan tidak masuk
     */
    public function getLaporanTidakMasuk(string $tanggalMulai, string $tanggalSelesai, ?int $pegawaiId = null): array
    {
        // Ambil semua pegawai aktif
        $queryPegawai = Pegawai::with('satpelkes')->where('status', 'aktif');
        if ($pegawaiId) {
            $queryPegawai->where('id', $pegawaiId);
        }
        $pegawaiList = $queryPegawai->get();

        $data = [];
        $tanggalMulaiCarbon = Carbon::parse($tanggalMulai);
        $tanggalSelesaiCarbon = Carbon::parse($tanggalSelesai);
        $hariIni = Carbon::today();

        // Loop setiap tanggal dalam periode
        $currentDate = $tanggalMulaiCarbon->copy();
        while ($currentDate->lte($tanggalSelesaiCarbon)) {
            // Hanya proses tanggal yang sudah terlewat (tanggal <= hari ini)
            if ($currentDate->lte($hariIni)) {
                // Hanya proses hari kerja (Senin-Jumat)
                if ($currentDate->dayOfWeek >= 1 && $currentDate->dayOfWeek <= 5) {
                $tanggal = $currentDate->format('Y-m-d');
                $hari = $currentDate->locale('id')->isoFormat('dddd');

                // Loop setiap pegawai
                foreach ($pegawaiList as $pegawai) {
                    // Cek apakah pegawai punya jadwal aktif pada tanggal ini
                    $jadwal = JadwalPegawai::where('pegawai_id', $pegawai->id)
                        ->aktif()
                        ->untukTanggal($tanggal)
                        ->untukHari($tanggal)
                        ->first();

                    // Jika ada jadwal, berarti pegawai seharusnya masuk
                    if ($jadwal) {
                        // Cek apakah ada presensi check_in pada tanggal ini
                        $presensiCheckIn = Presensi::where('pegawai_id', $pegawai->id)
                            ->where('tanggal', $tanggal)
                            ->where('jenis', 'check_in')
                            ->where('status', '!=', 'REJECTED')
                            ->first();

                        // Jika tidak ada presensi check_in
                        if (!$presensiCheckIn) {
                            // Cek apakah ada izin/cuti pada tanggal ini
                            $izinCuti = IzinCuti::where('pegawai_id', $pegawai->id)
                                ->where('tanggal', $tanggal)
                                ->first();

                            // Cek apakah ada tugas luar yang disetujui pada tanggal ini
                            $tugasLuar = TugasLuar::where('pegawai_id', $pegawai->id)
                                ->where('status', 'disetujui')
                                ->where(function ($q) use ($tanggal) {
                                    $q->where('tanggal_mulai', '<=', $tanggal)
                                      ->where('tanggal_selesai', '>=', $tanggal);
                                })
                                ->first();

                            // Prioritas: Izin/Cuti > Tugas Luar > Tidak Masuk
                            if ($izinCuti) {
                                // Jika izin/cuti sudah disetujui, jangan tampilkan di laporan tidak masuk
                                // karena sudah ada alasan yang valid
                                if ($izinCuti->status === 'disetujui') {
                                    continue; // Skip, tidak perlu ditampilkan
                                }
                                
                                // Jika izin/cuti ditolak atau pending, tampilkan
                                $statusKeterangan = ucfirst($izinCuti->jenis);
                                if ($izinCuti->status === 'ditolak') {
                                    $statusKeterangan .= ' (Ditolak)';
                                } else {
                                    $statusKeterangan .= ' (Pending)';
                                }

                                $data[] = [
                                    'pegawai_id' => $pegawai->id,
                                    'nip' => $pegawai->nip ?? '-',
                                    'nama' => $pegawai->nama ?? '-',
                                    'divisi' => $pegawai->divisi ?? '-',
                                    'jabatan' => $pegawai->jabatan ?? '-',
                                    'satpelkes_nama' => $pegawai->satpelkes->nama_satpelkes ?? '-',
                                    'tanggal' => $tanggal,
                                    'hari' => $hari,
                                    'jam_masuk' => $jadwal->jam_masuk ?? '-',
                                    'jam_keluar' => $jadwal->jam_keluar ?? '-',
                                    'keterangan' => $izinCuti->keterangan ?? ucfirst($izinCuti->jenis),
                                    'status_keterangan' => $statusKeterangan,
                                    'izin_cuti_id' => $izinCuti->id,
                                    'izin_cuti_status' => $izinCuti->status,
                                ];
                            } elseif ($tugasLuar) {
                                // Jika ada tugas luar yang disetujui, jangan tampilkan di laporan tidak masuk
                                // karena sudah ada alasan yang valid
                                continue; // Skip, tidak perlu ditampilkan
                            } else {
                                // Tidak ada izin/cuti dan tugas luar, maka tidak masuk
                                $data[] = [
                                    'pegawai_id' => $pegawai->id,
                                    'nip' => $pegawai->nip ?? '-',
                                    'nama' => $pegawai->nama ?? '-',
                                    'divisi' => $pegawai->divisi ?? '-',
                                    'jabatan' => $pegawai->jabatan ?? '-',
                                    'satpelkes_nama' => $pegawai->satpelkes->nama_satpelkes ?? '-',
                                    'tanggal' => $tanggal,
                                    'hari' => $hari,
                                    'jam_masuk' => $jadwal->jam_masuk ?? '-',
                                    'jam_keluar' => $jadwal->jam_keluar ?? '-',
                                    'keterangan' => 'Tidak ada presensi',
                                    'status_keterangan' => 'Tidak Masuk',
                                ];
                            }
                        }
                    }
                }
                }
            }

            $currentDate->addDay();
        }

        // Sort by tanggal descending, lalu nama
        usort($data, function ($a, $b) {
            if ($a['tanggal'] === $b['tanggal']) {
                return strcmp($a['nama'], $b['nama']);
            }
            return strcmp($b['tanggal'], $a['tanggal']);
        });

        return $data;
    }

    /**
     * Get laporan pulang cepat
     */
    public function getLaporanPulangCepat(string $tanggalMulai, string $tanggalSelesai, ?int $pegawaiId = null): array
    {
        $query = Presensi::with(['pegawai', 'satpelkes'])
            ->where('jenis', 'check_out')
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->where('status', '!=', 'REJECTED')
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_absen', 'asc');

        if ($pegawaiId) {
            $query->where('pegawai_id', $pegawaiId);
        }

        $presensiList = $query->get();
        $data = [];

        foreach ($presensiList as $presensi) {
            $jadwal = \App\Models\JadwalPegawai::where('pegawai_id', $presensi->pegawai_id)
                ->aktif()
                ->untukTanggal($presensi->tanggal)
                ->untukHari($presensi->tanggal)
                ->first();

            if ($jadwal) {
                $jamKeluarJadwal = Carbon::parse($jadwal->jam_keluar);
                $waktuCheckOut = Carbon::parse($presensi->waktu_absen);

                // Jika check-out lebih awal dari jam keluar jadwal
                if ($waktuCheckOut->lessThan($jamKeluarJadwal)) {
                    $menitCepat = (int) $waktuCheckOut->diffInMinutes($jamKeluarJadwal);
                    
                    // Ambil check_in untuk jam masuk
                    $checkIn = Presensi::where('pegawai_id', $presensi->pegawai_id)
                        ->where('tanggal', $presensi->tanggal)
                        ->where('jenis', 'check_in')
                        ->first();

                    $data[] = [
                        'nip' => $presensi->pegawai->nip ?? '-',
                        'nama' => $presensi->pegawai->nama ?? '-',
                        'divisi' => $presensi->pegawai->divisi ?? '-',
                        'jabatan' => $presensi->pegawai->jabatan ?? '-',
                        'satpelkes_nama' => $presensi->satpelkes->nama_satpelkes ?? '-',
                        'tanggal' => $presensi->tanggal->format('Y-m-d'),
                        'jam_masuk' => $checkIn ? $checkIn->waktu_absen->format('H:i:s') : '-',
                        'jam_keluar_jadwal' => $jadwal->jam_keluar,
                        'jam_checkout' => $presensi->waktu_absen->format('H:i:s'),
                        'menit_cepat' => $menitCepat,
                        'status_kehadiran' => $presensi->status,
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Get statistik dashboard
     */
    public function getStatistikDashboard(int $pegawaiId, ?string $bulan = null): array
    {
        if (!$bulan) {
            $bulan = Carbon::now()->format('Y-m');
        }

        $tanggalMulai = Carbon::parse($bulan)->startOfMonth()->format('Y-m-d');
        $tanggalSelesai = Carbon::parse($bulan)->endOfMonth()->format('Y-m-d');

        $presensi = Presensi::where('pegawai_id', $pegawaiId)
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->checkIn()
            ->get();

        $totalHadir = $presensi->whereIn('status', ['IN_ZONE', 'APPROVED'])->count();
        $totalTelat = DB::table('v_laporan_kehadiran')
            ->where('pegawai_id', $pegawaiId)
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->where('menit_telat', '>', 0)
            ->count();
        $totalPending = $presensi->where('status', 'OUT_ZONE_PENDING')->count();
        $totalRejected = $presensi->where('status', 'REJECTED')->count();

        $hariKerja = $this->getHariKerja($tanggalMulai, $tanggalSelesai);
        $persentaseKehadiran = $hariKerja > 0 ? ($totalHadir / $hariKerja) * 100 : 0;

        // Presensi hari ini
        $presensiHariIni = Presensi::where('pegawai_id', $pegawaiId)
            ->where('tanggal', Carbon::today())
            ->get();

        return [
            'bulan' => $bulan,
            'total_hadir' => $totalHadir,
            'total_terlambat' => $totalTelat,
            'total_pending' => $totalPending,
            'total_rejected' => $totalRejected,
            'persentase_kehadiran' => round($persentaseKehadiran, 2),
            'presensi_hari_ini' => [
                'check_in' => $presensiHariIni->where('jenis', 'check_in')->first()?->toArray(),
                'check_out' => $presensiHariIni->where('jenis', 'check_out')->first()?->toArray(),
            ],
        ];
    }

    /**
     * Get jumlah hari kerja dalam periode
     */
    protected function getHariKerja(string $tanggalMulai, string $tanggalSelesai): int
    {
        $mulai = Carbon::parse($tanggalMulai);
        $selesai = Carbon::parse($tanggalSelesai);
        $hari = 0;

        while ($mulai->lte($selesai)) {
            // Senin-Jumat
            if ($mulai->dayOfWeek >= 1 && $mulai->dayOfWeek <= 5) {
                $hari++;
            }
            $mulai->addDay();
        }

        return $hari;
    }

    /**
     * Get semua data presensi untuk export (termasuk yang tidak absen)
     */
    public function getAllPresensiForExport(string $tanggalMulai, string $tanggalSelesai, ?int $pegawaiId = null, ?string $jenisAbsen = null): array
    {
        // Ambil semua pegawai aktif
        $queryPegawai = Pegawai::with('satpelkes')->where('status', 'aktif');
        if ($pegawaiId) {
            $queryPegawai->where('id', $pegawaiId);
        }
        $pegawaiList = $queryPegawai->get();

        $tanggalMulaiCarbon = Carbon::parse($tanggalMulai);
        $tanggalSelesaiCarbon = Carbon::parse($tanggalSelesai);
        $hariIni = Carbon::today();

        $data = [];

        // Loop setiap tanggal dalam periode
        $currentDate = $tanggalMulaiCarbon->copy();
        while ($currentDate->lte($tanggalSelesaiCarbon)) {
            // Hanya proses tanggal yang sudah terlewat (tanggal <= hari ini)
            if ($currentDate->lte($hariIni)) {
                // Hanya proses hari kerja (Senin-Jumat)
                if ($currentDate->dayOfWeek >= 1 && $currentDate->dayOfWeek <= 5) {
                    $tanggal = $currentDate->format('Y-m-d');

                    // Loop setiap pegawai
                    foreach ($pegawaiList as $pegawai) {
                        // Cek apakah pegawai punya jadwal aktif pada tanggal ini
                        $jadwal = JadwalPegawai::where('pegawai_id', $pegawai->id)
                            ->aktif()
                            ->untukTanggal($tanggal)
                            ->untukHari($tanggal)
                            ->first();

                        // Jika ada jadwal, berarti pegawai seharusnya masuk
                        if ($jadwal) {
                            // Cek presensi check_in dan check_out
                            $presensiCheckIn = Presensi::where('pegawai_id', $pegawai->id)
                                ->where('tanggal', $tanggal)
                                ->where('jenis', 'check_in')
                                ->where('status', '!=', 'REJECTED')
                                ->first();

                            $presensiCheckOut = Presensi::where('pegawai_id', $pegawai->id)
                                ->where('tanggal', $tanggal)
                                ->where('jenis', 'check_out')
                                ->where('status', '!=', 'REJECTED')
                                ->first();

                            // Cek izin/cuti
                            $izinCuti = IzinCuti::where('pegawai_id', $pegawai->id)
                                ->where('tanggal', $tanggal)
                                ->first();

                            // Cek tugas luar
                            $tugasLuar = TugasLuar::where('pegawai_id', $pegawai->id)
                                ->where('status', 'disetujui')
                                ->where(function ($q) use ($tanggal) {
                                    $q->where('tanggal_mulai', '<=', $tanggal)
                                      ->where('tanggal_selesai', '>=', $tanggal);
                                })
                                ->first();

                            // Tentukan data
                            $jamMasuk = '-';
                            $jamPulang = '-';
                            $telatMasuk = '-';
                            $pulangCepat = '-';
                            $jenisAbsensi = 'Rutin';
                            $keterangan = '-';

                            // Jika ada presensi check_in
                            if ($presensiCheckIn) {
                                $jamMasuk = $presensiCheckIn->waktu_absen->format('H:i:s');
                                
                                // Hitung telat masuk
                                $jamMasukJadwal = Carbon::parse($jadwal->jam_masuk);
                                $waktuCheckIn = Carbon::parse($presensiCheckIn->waktu_absen);
                                $batasWaktu = $jamMasukJadwal->copy()->addMinutes($jadwal->toleransi_telat);

                                if ($waktuCheckIn->greaterThan($batasWaktu)) {
                                    $menitTelat = (int) $waktuCheckIn->diffInMinutes($batasWaktu);
                                    $telatMasuk = $menitTelat . ' menit';
                                }
                            }

                            // Jika ada presensi check_out
                            if ($presensiCheckOut) {
                                $jamPulang = $presensiCheckOut->waktu_absen->format('H:i:s');
                                
                                // Hitung pulang cepat
                                $jamKeluarJadwal = Carbon::parse($jadwal->jam_keluar);
                                $waktuCheckOut = Carbon::parse($presensiCheckOut->waktu_absen);

                                if ($waktuCheckOut->lessThan($jamKeluarJadwal)) {
                                    $menitCepat = (int) $waktuCheckOut->diffInMinutes($jamKeluarJadwal);
                                    $pulangCepat = $menitCepat . ' menit';
                                }
                            }

                            // Tentukan jenis absensi dan keterangan
                            if ($izinCuti && $izinCuti->status === 'disetujui') {
                                $jenisAbsensi = ucfirst($izinCuti->jenis);
                                $keterangan = $izinCuti->keterangan ?? ucfirst($izinCuti->jenis);
                            } elseif ($tugasLuar) {
                                $jenisAbsensi = 'Tugas Luar';
                                $keterangan = $tugasLuar->keterangan ?? 'Tugas Luar';
                            } elseif (!$presensiCheckIn) {
                                $jenisAbsensi = 'Tidak Masuk';
                                $keterangan = 'Tidak ada presensi';
                            } elseif ($presensiCheckIn && $presensiCheckIn->keterangan) {
                                $keterangan = $presensiCheckIn->keterangan;
                            }

                            // Buat row data
                            $data[] = [
                                'no' => 0, // Akan diisi setelah sorting
                                'nip' => $pegawai->nip ?? '-',
                                'nama_pegawai' => $pegawai->nama ?? '-',
                                'unit_kerja' => $pegawai->satpelkes->nama_satpelkes ?? '-',
                                'jenis_absensi' => $jenisAbsensi,
                                'tanggal_absen' => $currentDate->format('d/m/Y'),
                                'jam_masuk' => $jamMasuk,
                                'jam_pulang' => $jamPulang,
                                'telat_masuk' => $telatMasuk,
                                'pulang_cepat' => $pulangCepat,
                                'keterangan' => $keterangan,
                            ];
                        }
                    }
                }
            }

            $currentDate->addDay();
        }

        // Filter berdasarkan jenis absen jika ada
        if ($jenisAbsen && $jenisAbsen !== '') {
            $data = array_filter($data, function ($row) use ($jenisAbsen) {
                return $row['jenis_absensi'] === $jenisAbsen;
            });
            // Re-index array setelah filter
            $data = array_values($data);
        }

        // Urutkan berdasarkan nama pegawai (A-Z)
        usort($data, function ($a, $b) {
            return strcmp($a['nama_pegawai'], $b['nama_pegawai']);
        });

        // Isi nomor urut setelah sorting
        $no = 1;
        foreach ($data as &$row) {
            $row['no'] = $no++;
        }

        return $data;
    }
}

