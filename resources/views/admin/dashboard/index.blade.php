@extends('layouts.app')

@section('title', 'Dashboard - Sistem Absensi PPKP')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-home me-2"></i>Dashboard</h2>
    <span class="text-muted">{{ now()->format('d F Y, H:i') }}</span>
</div>

<!-- Statistics Cards -->
@if($userRole === 'pegawai')
    {{-- Statistik untuk Pegawai (5 cards: 3 + 2) --}}
    <div class="row mb-3">
        <div class="col-md-4 mb-3">
            <div class="stats-card stats-card-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Presensi Hari Ini</p>
                        <h3>{{ $stats['presensi_hari_ini'] }}</h3>
                    </div>
                    <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="stats-card stats-card-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Approve Bulan Ini</p>
                        <h3>{{ $stats['approve_bulan_ini'] }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="stats-card stats-card-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Telat Hari Ini</p>
                        <h3>{{ $stats['telat_hari_ini'] }}</h3>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="stats-card stats-card-orange">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Tidak Absen Bulan Ini</p>
                        <h3>{{ $stats['tidak_absen_bulan_ini'] }}</h3>
                    </div>
                    <i class="fas fa-user-slash fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="stats-card stats-card-purple">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Pulang Cepat Bulan Ini</p>
                        <h3>{{ $stats['pulang_cepat_bulan_ini'] }}</h3>
                    </div>
                    <i class="fas fa-arrow-left fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Statistik untuk Admin/Pimpinan (7 cards: 4 + 3) --}}
    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <div class="stats-card stats-card-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Total Pegawai</p>
                        <h3>{{ $stats['total_pegawai'] }}</h3>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stats-card stats-card-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Presensi Hari Ini</p>
                        <h3>{{ $stats['presensi_hari_ini'] }}</h3>
                    </div>
                    <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stats-card stats-card-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Pending Approval</p>
                        <h3>{{ $stats['pending_approval'] }}</h3>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stats-card stats-card-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Approve Bulan Ini</p>
                        <h3>{{ $stats['approve_bulan_ini'] }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stats-card stats-card-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Telat Hari Ini</p>
                        <h3>{{ $stats['telat_hari_ini'] }}</h3>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="stats-card stats-card-orange">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Tidak Absen Hari Ini</p>
                        <h3>{{ $stats['tidak_absen_bulan_ini'] }}</h3>
                    </div>
                    <i class="fas fa-user-slash fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="stats-card stats-card-purple">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Pulang Cepat Bulan Ini</p>
                        <h3>{{ $stats['pulang_cepat_bulan_ini'] }}</h3>
                    </div>
                    <i class="fas fa-arrow-left fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Chart -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i>Presensi 7 Hari Terakhir
            </div>
            <div class="card-body">
                <canvas id="presensiChart" height="60"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Presensi -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>Presensi Terbaru</span>
                <a href="{{ route('presensi.index') }}" class="btn btn-sm btn-light">
                    Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pegawai</th>
                                <th>Jenis</th>
                                <th>Waktu</th>
                                <th>Unit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPresensi as $presensi)
                                <tr>
                                    <td>{{ $presensi->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        <strong>{{ $presensi->pegawai->nama }}</strong><br>
                                        <small class="text-muted">{{ $presensi->pegawai->nip }}</small>
                                    </td>
                                    <td>
                                        @if($presensi->jenis === 'check_in')
                                            <span class="badge bg-success">Absen Masuk</span>
                                        @else
                                            <span class="badge bg-info">Absen Pulang</span>
                                        @endif
                                    </td>
                                    <td>{{ $presensi->waktu_absen->format('H:i:s') }}</td>
                                    <td>{{ $presensi->satpelkes->nama_satpelkes ?? '-' }}</td>
                                    <td>
                                        @if($presensi->status === 'IN_ZONE')
                                            <span class="badge bg-success">IN ZONE</span>
                                        @elseif($presensi->status === 'OUT_ZONE_PENDING')
                                            <span class="badge bg-warning">PENDING</span>
                                        @elseif($presensi->status === 'APPROVED')
                                            <span class="badge bg-primary">APPROVED</span>
                                        @elseif($presensi->status === 'REJECTED')
                                            <span class="badge bg-danger">REJECTED</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Belum ada data presensi
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Chart Presensi
    const ctx = document.getElementById('presensiChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.label),
            datasets: [{
                label: 'Jumlah Presensi',
                data: chartData.map(item => item.count),
                borderColor: '#2c5aa0',
                backgroundColor: 'rgba(44, 90, 160, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection

