<?php

namespace App\Services;

use App\Models\IzinCuti;
use App\Models\JadwalPegawai;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\TugasLuar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            if ($jadwal && $jadwal->jam_masuk) {
                // Parse jam_masuk dari jadwal (format: H:i atau H:i:s)
                $jamMasukJadwal = Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk.':00')->setDateFrom($presensi->tanggal);
                $waktuCheckIn = Carbon::parse($presensi->waktu_absen);
                // Batas waktu = jam_masuk + toleransi_telat (dalam menit)
                $batasWaktu = $jamMasukJadwal->copy()->addMinutes($jadwal->toleransi_telat ?? 0);

                // Jika check-in lebih dari batas waktu (setelah toleransi), maka telat
                if ($waktuCheckIn->greaterThan($batasWaktu)) {
                    // Hitung selisih menit antara waktu check-in dengan batas waktu
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
                            if (! $presensiCheckIn) {
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

            if ($jadwal && $jadwal->jam_keluar) {
                try {
                    // Parse jam_keluar dari jadwal - handle berbagai format
                    $jamKeluarStr = $jadwal->jam_keluar;
                    if (strlen($jamKeluarStr) == 5) {
                        // Format H:i
                        $jamKeluarJadwal = Carbon::createFromFormat('H:i', $jamKeluarStr)->setDateFrom($presensi->tanggal);
                    } else {
                        // Format H:i:s atau lainnya
                        $jamKeluarJadwal = Carbon::parse($presensi->tanggal->format('Y-m-d').' '.$jamKeluarStr);
                    }
                    $waktuCheckOut = Carbon::parse($presensi->waktu_absen);
                } catch (\Exception $e) {
                    continue; // Skip jika error parsing
                }

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
        if (! $bulan) {
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
                                // Parse jam_masuk dari jadwal (format: H:i atau H:i:s)
                                $jamMasukJadwal = Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk.':00')->setDateFrom($tanggal);
                                $waktuCheckIn = Carbon::parse($presensiCheckIn->waktu_absen);
                                // Batas waktu = jam_masuk + toleransi_telat (dalam menit)
                                $batasWaktu = $jamMasukJadwal->copy()->addMinutes($jadwal->toleransi_telat ?? 0);

                                if ($waktuCheckIn->greaterThan($batasWaktu)) {
                                    $menitTelat = (int) $waktuCheckIn->diffInMinutes($batasWaktu);
                                    $telatMasuk = $menitTelat.' menit';
                                }
                            }

                            // Jika ada presensi check_out
                            if ($presensiCheckOut) {
                                $jamPulang = $presensiCheckOut->waktu_absen->format('H:i:s');

                                // Hitung pulang cepat
                                // Parse jam_keluar dari jadwal (format: H:i atau H:i:s)
                                $jamKeluarJadwal = Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar.':00')->setDateFrom($tanggal);
                                $waktuCheckOut = Carbon::parse($presensiCheckOut->waktu_absen);

                                if ($waktuCheckOut->lessThan($jamKeluarJadwal)) {
                                    $menitCepat = (int) $waktuCheckOut->diffInMinutes($jamKeluarJadwal);
                                    $pulangCepat = $menitCepat.' menit';
                                }
                            }

                            // Tentukan jenis absensi dan keterangan
                            if ($izinCuti && $izinCuti->status === 'disetujui') {
                                $jenisAbsensi = ucfirst($izinCuti->jenis);
                                $keterangan = $izinCuti->keterangan ?? ucfirst($izinCuti->jenis);
                            } elseif ($tugasLuar) {
                                $jenisAbsensi = 'Tugas Luar';
                                $keterangan = $tugasLuar->keterangan ?? 'Tugas Luar';
                            } elseif (! $presensiCheckIn) {
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

    /**
     * Get akumulasi laporan per bulan per pegawai
     */
    public function getAkumulasiBulanan(string $bulan, ?int $pegawaiId = null): array
    {
        $tanggalMulai = Carbon::parse($bulan)->startOfMonth()->format('Y-m-d');
        $tanggalSelesai = Carbon::parse($bulan)->endOfMonth()->format('Y-m-d');

        // Ambil semua pegawai aktif
        $queryPegawai = Pegawai::with('satpelkes')->where('status', 'aktif');
        if ($pegawaiId) {
            $queryPegawai->where('id', $pegawaiId);
        }
        $pegawaiList = $queryPegawai->get();

        $data = [];
        $hariIni = Carbon::today();

        foreach ($pegawaiList as $pegawai) {
            // Total Absensi (check_in yang approved/in_zone)
            $totalAbsensi = Presensi::where('pegawai_id', $pegawai->id)
                ->where('jenis', 'check_in')
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->whereIn('status', ['IN_ZONE', 'APPROVED'])
                ->count();

            // Cek apakah pegawai punya jadwal aktif
            $hasJadwal = JadwalPegawai::where('pegawai_id', $pegawai->id)
                ->aktif()
                ->exists();

            // Total Keterlambatan (dalam menit)
            $presensiTelat = Presensi::where('pegawai_id', $pegawai->id)
                ->where('jenis', 'check_in')
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->whereIn('status', ['IN_ZONE', 'APPROVED'])
                ->get();

            $totalMenitTelat = 0;
            $jumlahTelat = 0;
            foreach ($presensiTelat as $presensi) {
                $jadwal = JadwalPegawai::where('pegawai_id', $pegawai->id)
                    ->aktif()
                    ->untukTanggal($presensi->tanggal)
                    ->untukHari($presensi->tanggal)
                    ->first();

                if ($jadwal && $jadwal->jam_masuk) {
                    try {
                        // Parse jam_masuk dari jadwal - handle berbagai format
                        $jamMasukStr = $jadwal->jam_masuk;
                        if (strlen($jamMasukStr) == 5) {
                            // Format H:i
                            $jamMasukJadwal = Carbon::createFromFormat('H:i', $jamMasukStr)->setDateFrom($presensi->tanggal);
                        } else {
                            // Format H:i:s atau lainnya
                            $jamMasukJadwal = Carbon::parse($presensi->tanggal->format('Y-m-d').' '.$jamMasukStr);
                        }

                        $waktuCheckIn = Carbon::parse($presensi->waktu_absen);

                        // Batas waktu = jam_masuk + toleransi_telat (dalam menit)
                        $toleransi = $jadwal->toleransi_telat ?? 0;
                        $batasWaktu = $jamMasukJadwal->copy()->addMinutes($toleransi);

                        // Jika check-in lebih dari batas waktu (setelah toleransi), maka telat
                        if ($waktuCheckIn->greaterThan($batasWaktu)) {
                            // Hitung selisih menit antara waktu check-in dengan batas waktu
                            $menitTelat = (int) $waktuCheckIn->diffInMinutes($batasWaktu);
                            $totalMenitTelat += $menitTelat;
                            $jumlahTelat++;
                        }
                    } catch (\Exception $e) {
                        // Skip jika ada error parsing
                        Log::warning('Error parsing jam_masuk jadwal', [
                            'pegawai_id' => $pegawai->id,
                            'jadwal_id' => $jadwal->id,
                            'jam_masuk' => $jadwal->jam_masuk,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Total Pulang Cepat (dalam menit)
            $presensiPulangCepat = Presensi::where('pegawai_id', $pegawai->id)
                ->where('jenis', 'check_out')
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->whereIn('status', ['IN_ZONE', 'APPROVED'])
                ->get();

            $totalMenitPulangCepat = 0;
            $jumlahPulangCepat = 0;
            foreach ($presensiPulangCepat as $presensi) {
                $jadwal = JadwalPegawai::where('pegawai_id', $pegawai->id)
                    ->aktif()
                    ->untukTanggal($presensi->tanggal)
                    ->untukHari($presensi->tanggal)
                    ->first();

                if ($jadwal && $jadwal->jam_keluar) {
                    try {
                        // Parse jam_keluar dari jadwal - handle berbagai format
                        $jamKeluarStr = $jadwal->jam_keluar;
                        if (strlen($jamKeluarStr) == 5) {
                            // Format H:i
                            $jamKeluarJadwal = Carbon::createFromFormat('H:i', $jamKeluarStr)->setDateFrom($presensi->tanggal);
                        } else {
                            // Format H:i:s atau lainnya
                            $jamKeluarJadwal = Carbon::parse($presensi->tanggal->format('Y-m-d').' '.$jamKeluarStr);
                        }

                        $waktuCheckOut = Carbon::parse($presensi->waktu_absen);

                        // Jika check-out lebih awal dari jam_keluar jadwal, maka pulang cepat
                        if ($waktuCheckOut->lessThan($jamKeluarJadwal)) {
                            // Hitung selisih menit antara jam_keluar jadwal dengan waktu check-out
                            $menitCepat = (int) $waktuCheckOut->diffInMinutes($jamKeluarJadwal);
                            $totalMenitPulangCepat += $menitCepat;
                            $jumlahPulangCepat++;
                        }
                    } catch (\Exception $e) {
                        // Skip jika ada error parsing
                        Log::warning('Error parsing jam_keluar jadwal', [
                            'pegawai_id' => $pegawai->id,
                            'jadwal_id' => $jadwal->id,
                            'jam_keluar' => $jadwal->jam_keluar,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Total Tidak Masuk
            $tanggalMulaiCarbon = Carbon::parse($tanggalMulai);
            $tanggalSelesaiCarbon = Carbon::parse($tanggalSelesai);
            $totalTidakMasuk = 0;
            $currentDate = $tanggalMulaiCarbon->copy();

            while ($currentDate->lte($tanggalSelesaiCarbon)) {
                if ($currentDate->lte($hariIni) && $currentDate->dayOfWeek >= 1 && $currentDate->dayOfWeek <= 5) {
                    $tanggal = $currentDate->format('Y-m-d');
                    $jadwal = JadwalPegawai::where('pegawai_id', $pegawai->id)
                        ->aktif()
                        ->untukTanggal($tanggal)
                        ->untukHari($tanggal)
                        ->first();

                    if ($jadwal) {
                        $presensiCheckIn = Presensi::where('pegawai_id', $pegawai->id)
                            ->where('tanggal', $tanggal)
                            ->where('jenis', 'check_in')
                            ->where('status', '!=', 'REJECTED')
                            ->first();

                        if (! $presensiCheckIn) {
                            // Cek apakah ada izin/cuti yang disetujui
                            $izinCuti = IzinCuti::where('pegawai_id', $pegawai->id)
                                ->where('tanggal', $tanggal)
                                ->where('status', 'disetujui')
                                ->first();

                            // Cek apakah ada tugas luar yang disetujui
                            $tugasLuar = TugasLuar::where('pegawai_id', $pegawai->id)
                                ->where('status', 'disetujui')
                                ->where(function ($q) use ($tanggal) {
                                    $q->where('tanggal_mulai', '<=', $tanggal)
                                        ->where('tanggal_selesai', '>=', $tanggal);
                                })
                                ->first();

                            // Jika tidak ada izin/cuti atau tugas luar yang disetujui, maka tidak masuk
                            if (! $izinCuti && ! $tugasLuar) {
                                $totalTidakMasuk++;
                            }
                        }
                    }
                }
                $currentDate->addDay();
            }

            // Total Cuti
            $totalCuti = IzinCuti::where('pegawai_id', $pegawai->id)
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->where('status', 'disetujui')
                ->where('jenis', 'cuti')
                ->count();

            // Total Izin
            $totalIzin = IzinCuti::where('pegawai_id', $pegawai->id)
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->where('status', 'disetujui')
                ->where('jenis', 'izin')
                ->count();

            // Total Sakit
            $totalSakit = IzinCuti::where('pegawai_id', $pegawai->id)
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->where('status', 'disetujui')
                ->where('jenis', 'sakit')
                ->count();

            // Total Tugas Luar
            $totalTugasLuar = TugasLuar::where('pegawai_id', $pegawai->id)
                ->where('status', 'disetujui')
                ->where(function ($q) use ($tanggalMulai, $tanggalSelesai) {
                    $q->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                        ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
                        ->orWhere(function ($query) use ($tanggalMulai, $tanggalSelesai) {
                            $query->where('tanggal_mulai', '<=', $tanggalMulai)
                                ->where('tanggal_selesai', '>=', $tanggalSelesai);
                        });
                })
                ->count();

            // Hitung hari kerja dalam bulan
            $hariKerja = $this->getHariKerja($tanggalMulai, $tanggalSelesai);

            $data[] = [
                'pegawai_id' => $pegawai->id,
                'nip' => $pegawai->nip ?? '-',
                'nama' => $pegawai->nama ?? '-',
                'jabatan' => $pegawai->jabatan ?? '-',
                'satpelkes_nama' => $pegawai->satpelkes->nama_satpelkes ?? '-',
                'total_absensi' => $totalAbsensi,
                'total_menit_telat' => $totalMenitTelat,
                'jumlah_telat' => $jumlahTelat,
                'total_menit_pulang_cepat' => $totalMenitPulangCepat,
                'jumlah_pulang_cepat' => $jumlahPulangCepat,
                'total_tidak_masuk' => $totalTidakMasuk,
                'total_cuti' => $totalCuti,
                'total_izin' => $totalIzin,
                'total_sakit' => $totalSakit,
                'total_tugas_luar' => $totalTugasLuar,
                'hari_kerja' => $hariKerja,
                'persentase_kehadiran' => $hariKerja > 0 ? round(($totalAbsensi / $hariKerja) * 100, 2) : 0,
            ];
        }

        // Sort by nama
        usort($data, function ($a, $b) {
            return strcmp($a['nama'], $b['nama']);
        });

        return $data;
    }
}
