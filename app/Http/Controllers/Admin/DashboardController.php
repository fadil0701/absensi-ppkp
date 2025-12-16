<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Satpelkes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth('web')->user();
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $userRole = $user->role;
        
        // Query base untuk presensi
        $presensiQuery = Presensi::query();
        $pegawaiQuery = Pegawai::query();
        
        // Jika role pegawai, filter hanya untuk user tersebut
        if ($userRole === 'pegawai') {
            $presensiQuery->where('pegawai_id', $user->id);
            $pegawaiId = $user->id;
        } else {
            $pegawaiId = null;
        }
        
        // Statistics berdasarkan role
        if ($userRole === 'pegawai') {
            // Statistik untuk pegawai
            $stats = [
                'presensi_hari_ini' => (clone $presensiQuery)->where('tanggal', $today)->checkIn()->count(),
                'approve_bulan_ini' => (clone $presensiQuery)
                    ->where('tanggal', '>=', $thisMonth)
                    ->where('tanggal', '<=', $today)
                    ->whereIn('status', ['APPROVED', 'IN_ZONE'])
                    ->count(),
                'telat_hari_ini' => $this->hitungTelatHariIni($user->id),
                'tidak_absen_bulan_ini' => $this->hitungTidakAbsenBulanIni($user->id),
                'pulang_cepat_bulan_ini' => $this->hitungPulangCepatBulanIni($user->id),
            ];
        } else {
            // Statistik untuk admin/pimpinan
            $stats = [
                'total_pegawai' => Pegawai::count(),
                'total_satpelkes' => Satpelkes::where('is_aktif', true)->count(),
                'presensi_hari_ini' => Presensi::where('tanggal', $today)->checkIn()->count(),
                'pending_approval' => Presensi::where('status', 'OUT_ZONE_PENDING')->count(),
                'approve_bulan_ini' => Presensi::where('tanggal', '>=', $thisMonth)
                    ->where('tanggal', '<=', $today)
                    ->whereIn('status', ['APPROVED', 'IN_ZONE'])
                    ->count(),
                'telat_hari_ini' => $this->hitungTelatHariIni(),
                'tidak_absen_bulan_ini' => $this->hitungTidakAbsenBulanIni(),
                'pulang_cepat_bulan_ini' => $this->hitungPulangCepatBulanIni(),
            ];
        }

        // Recent presensi (filter berdasarkan role)
        $recentPresensi = (clone $presensiQuery)
            ->with(['pegawai', 'satpelkes'])
            ->latest('waktu_absen')
            ->limit(10)
            ->get();

        // Chart data (presensi per hari dalam 7 hari terakhir)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartQuery = (clone $presensiQuery)->where('tanggal', $date)->checkIn();
            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'count' => $chartQuery->count(),
            ];
        }

        return view('admin.dashboard.index', compact('stats', 'recentPresensi', 'chartData', 'userRole'));
    }
    
    /**
     * Hitung jumlah telat hari ini
     */
    private function hitungTelatHariIni(?int $pegawaiId = null): int
    {
        $today = Carbon::today();
        $query = Presensi::where('tanggal', $today)
            ->checkIn()
            ->where('status', '!=', 'REJECTED');
            
        if ($pegawaiId) {
            $query->where('pegawai_id', $pegawaiId);
        }
        
        $presensiList = $query->get();
        $telatCount = 0;
        
        foreach ($presensiList as $presensi) {
            $jadwal = \App\Models\JadwalPegawai::where('pegawai_id', $presensi->pegawai_id)
                ->aktif()
                ->untukTanggal($today)
                ->untukHari($today)
                ->first();
                
            if ($jadwal) {
                $jamMasuk = Carbon::parse($jadwal->jam_masuk);
                $jamCheckIn = Carbon::parse($presensi->waktu_absen);
                $batasWaktu = $jamMasuk->copy()->addMinutes($jadwal->toleransi_telat);
                
                if ($jamCheckIn->greaterThan($batasWaktu)) {
                    $telatCount++;
                }
            }
        }
        
        return $telatCount;
    }
    
    /**
     * Hitung jumlah tidak absen bulan ini
     */
    private function hitungTidakAbsenBulanIni(?int $pegawaiId = null): int
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $today = Carbon::today();
        
        if ($pegawaiId) {
            // Hitung hari kerja yang tidak ada presensi check-in
            $pegawai = Pegawai::find($pegawaiId);
            $hadirDates = Presensi::where('pegawai_id', $pegawaiId)
                ->where('tanggal', '>=', $thisMonth)
                ->where('tanggal', '<=', $today)
                ->checkIn()
                ->where('status', '!=', 'REJECTED')
                ->pluck('tanggal')
                ->map(function ($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                })
                ->toArray();
                
            // Hitung hari kerja (Senin-Jumat) dalam bulan ini sampai hari ini
            $hariKerja = 0;
            $current = $thisMonth->copy();
            while ($current->lte($today)) {
                if ($current->dayOfWeek >= 1 && $current->dayOfWeek <= 5) { // Senin-Jumat
                    if (!in_array($current->format('Y-m-d'), $hadirDates)) {
                        $hariKerja++;
                    }
                }
                $current->addDay();
            }
            
            return $hariKerja;
        } else {
            // Untuk admin/pimpinan: hitung total pegawai yang tidak absen hari ini
            $hadirToday = Presensi::where('tanggal', $today)
                ->checkIn()
                ->where('status', '!=', 'REJECTED')
                ->distinct('pegawai_id')
                ->count('pegawai_id');
                
            $totalPegawaiAktif = Pegawai::where('status', 'aktif')->count();
            
            return max(0, $totalPegawaiAktif - $hadirToday);
        }
    }
    
    /**
     * Hitung jumlah pulang cepat bulan ini
     */
    private function hitungPulangCepatBulanIni(?int $pegawaiId = null): int
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $today = Carbon::today();
        
        $query = Presensi::where('tanggal', '>=', $thisMonth)
            ->where('tanggal', '<=', $today)
            ->checkOut()
            ->where('status', '!=', 'REJECTED');
            
        if ($pegawaiId) {
            $query->where('pegawai_id', $pegawaiId);
        }
        
        $presensiList = $query->get();
        $pulangCepatCount = 0;
        
        foreach ($presensiList as $presensi) {
            $jadwal = \App\Models\JadwalPegawai::where('pegawai_id', $presensi->pegawai_id)
                ->aktif()
                ->untukTanggal($presensi->tanggal)
                ->untukHari($presensi->tanggal)
                ->first();
                
            if ($jadwal) {
                $jamKeluar = Carbon::parse($jadwal->jam_keluar);
                $jamCheckOut = Carbon::parse($presensi->waktu_absen);
                
                // Jika check-out lebih awal dari jam keluar jadwal
                if ($jamCheckOut->lessThan($jamKeluar)) {
                    $pulangCepatCount++;
                }
            }
        }
        
        return $pulangCepatCount;
    }
}

