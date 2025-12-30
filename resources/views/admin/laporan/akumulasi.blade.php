@extends('layouts.app')

@section('title', 'Laporan Akumulasi Bulanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h2><i class="fas fa-chart-line me-2"></i>Laporan Akumulasi Bulanan</h2>
    <div>
        <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>
</div>

<!-- Form Filter -->
<div class="card mb-3 no-print">
    <div class="card-body">
        <form action="{{ route('laporan.akumulasi') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Bulan</label>
                <input type="month" name="bulan" class="form-control form-control-sm" 
                       value="{{ $bulan }}" required>
            </div>
            @if(auth('web')->user()->role !== 'pegawai')
                <div class="col-md-4">
                    <label class="form-label small">Pegawai</label>
                    <select name="pegawai_id" class="form-select form-select-sm">
                        <option value="">Semua Pegawai</option>
                        @foreach($pegawaiList as $pegawai)
                            <option value="{{ $pegawai->id }}" {{ request('pegawai_id') == $pegawai->id ? 'selected' : '' }}>
                                {{ $pegawai->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Header untuk Print -->
<div class="print-header">
    <table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 15px;">
        <tr>
            <td style="text-align: center; padding: 10px 0;">
                <h3 style="margin: 0; font-size: 18px; font-weight: bold;">Sistem Absensi PPKP</h3>
                <h4 style="margin: 5px 0; font-size: 16px; font-weight: bold;">Laporan Akumulasi Bulanan</h4>
                <p style="margin: 5px 0; font-size: 12px;">
                    Periode: {{ \Carbon\Carbon::parse($bulan)->locale('id')->isoFormat('MMMM YYYY') }}
                </p>
            </td>
        </tr>
    </table>
</div>

<!-- Tabel Data -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 print-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>NIP</th>
                        <th>Nama Pegawai</th>
                        <th>Jabatan</th>
                        <th>Unit Kerja</th>
                        <th>Total Absensi</th>
                        <th>Keterlambatan</th>
                        <th>Pulang Cepat</th>
                        <th>Tidak Masuk</th>
                        <th>Cuti</th>
                        <th>Izin</th>
                        <th>Sakit</th>
                        <th>Tugas Luar</th>
                        <th>% Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><strong>{{ $item['nip'] }}</strong></td>
                            <td>{{ $item['nama'] }}</td>
                            <td>{{ $item['jabatan'] ?? '-' }}</td>
                            <td>{{ $item['satpelkes_nama'] ?? '-' }}</td>
                            <td class="text-center">
                                <strong>{{ $item['total_absensi'] }}</strong>
                                <span class="d-print-none"><br><small class="text-muted">dari {{ $item['hari_kerja'] }} hari</small></span>
                                <span class="d-none d-print-inline">/{{ $item['hari_kerja'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($item['jumlah_telat'] > 0)
                                    <span class="badge bg-warning d-print-none">{{ $item['jumlah_telat'] }}x</span>
                                    <span class="d-none d-print-inline">{{ $item['jumlah_telat'] }}x</span>
                                    <br>
                                    <small>{{ number_format($item['total_menit_telat'], 0) }} menit</small>
                                @else
                                    <span class="text-success">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['jumlah_pulang_cepat'] > 0)
                                    <span class="badge bg-info d-print-none">{{ $item['jumlah_pulang_cepat'] }}x</span>
                                    <span class="d-none d-print-inline">{{ $item['jumlah_pulang_cepat'] }}x</span>
                                    <br>
                                    <small>{{ number_format($item['total_menit_pulang_cepat'], 0) }} menit</small>
                                @else
                                    <span class="text-success">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['total_tidak_masuk'] > 0)
                                    <span class="badge bg-danger d-print-none">{{ $item['total_tidak_masuk'] }}</span>
                                    <span class="d-none d-print-inline">{{ $item['total_tidak_masuk'] }}</span>
                                @else
                                    <span class="text-success">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['total_cuti'] > 0)
                                    <span class="badge bg-warning d-print-none">{{ $item['total_cuti'] }}</span>
                                    <span class="d-none d-print-inline">{{ $item['total_cuti'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['total_izin'] > 0)
                                    <span class="badge bg-primary d-print-none">{{ $item['total_izin'] }}</span>
                                    <span class="d-none d-print-inline">{{ $item['total_izin'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['total_sakit'] > 0)
                                    <span class="badge bg-danger d-print-none">{{ $item['total_sakit'] }}</span>
                                    <span class="d-none d-print-inline">{{ $item['total_sakit'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['total_tugas_luar'] > 0)
                                    <span class="badge bg-info d-print-none">{{ $item['total_tugas_luar'] }}</span>
                                    <span class="d-none d-print-inline">{{ $item['total_tugas_luar'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['persentase_kehadiran'] >= 90)
                                    <span class="badge bg-success d-print-none">{{ $item['persentase_kehadiran'] }}%</span>
                                    <span class="d-none d-print-inline">{{ $item['persentase_kehadiran'] }}%</span>
                                @elseif($item['persentase_kehadiran'] >= 75)
                                    <span class="badge bg-warning d-print-none">{{ $item['persentase_kehadiran'] }}%</span>
                                    <span class="d-none d-print-inline">{{ $item['persentase_kehadiran'] }}%</span>
                                @else
                                    <span class="badge bg-danger d-print-none">{{ $item['persentase_kehadiran'] }}%</span>
                                    <span class="d-none d-print-inline">{{ $item['persentase_kehadiran'] }}%</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-4">
                                <p class="text-muted mb-0">Tidak ada data akumulasi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="print-footer">
                    <tr>
                        <td colspan="14" style="border-top: 2px solid #000; padding: 10px 0;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="width: 50%; text-align: left; font-size: 11px;">
                                        Dicetak pada: {{ now()->format('d F Y H:i:s') }}
                                    </td>
                                    <td style="width: 50%; text-align: right; font-size: 11px;">
                                        Halaman: <span class="page-number"></span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        
        .print-header {
            display: block !important;
            page-break-inside: avoid;
        }
        
        .print-footer {
            display: table-footer-group !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .table {
            font-size: 10px;
            border-collapse: collapse !important;
            width: 100%;
        }
        
        .table th,
        .table td {
            padding: 5px 6px;
            border: 1px solid #000 !important;
            text-align: left;
        }
        
        .table thead th {
            background-color: #e0e0e0 !important;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000 !important;
        }
        
        .table tbody tr {
            border: 1px solid #000 !important;
        }
        
        .table tbody td {
            border: 1px solid #000 !important;
        }
        
        .table tfoot td {
            border: 1px solid #000 !important;
            background-color: #f5f5f5 !important;
        }
        
        body {
            padding: 0;
            margin: 0;
            font-size: 10px;
        }
        
        @page {
            margin: 1.5cm;
            size: A4 landscape;
        }
        
        /* Header dan Footer di setiap halaman */
        @page {
            @top-center {
                content: "Sistem Absensi PPKP - Laporan Akumulasi Bulanan";
                font-size: 10px;
            }
            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
                font-size: 9px;
            }
        }
    }
    
    .print-header {
        display: none;
    }
    
    .print-table {
        font-size: 12px;
    }
    
    .print-footer {
        display: none;
    }
</style>
@endpush

<script>
// Update page number saat print
window.addEventListener('beforeprint', function() {
    const pageNumbers = document.querySelectorAll('.page-number');
    pageNumbers.forEach(function(el) {
        el.textContent = '1'; // Akan diupdate oleh browser saat print
    });
});
</script>
@endsection

