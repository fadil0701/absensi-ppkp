@extends('layouts.app')

@section('title', 'Laporan Tidak Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h2><i class="fas fa-user-times me-2"></i>Laporan Tidak Masuk</h2>
    <div>
        <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>
</div>

<!-- Header untuk Print -->
<div class="print-header">
    <table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 15px;">
        <tr>
            <td style="text-align: center; padding: 10px 0;">
                <h3 style="margin: 0; font-size: 18px; font-weight: bold;">Sistem Absensi PPKP</h3>
                <h4 style="margin: 5px 0; font-size: 16px; font-weight: bold;">Laporan Tidak Masuk</h4>
                <p style="margin: 5px 0; font-size: 12px;">
                    Periode: {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d F Y') }} s/d {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d F Y') }}
                </p>
                <p style="margin: 0; font-size: 12px;">
                    Total: {{ count($data) }} hari tidak masuk
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="card">
    <div class="card-body">
        @if(count($data) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-sm print-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>NIP</th>
                            <th>Nama Pegawai</th>
                            <th>Jabatan</th>
                            <th>Unit Kerja</th>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th class="no-print" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><strong>{{ $item['nip'] }}</strong></td>
                                <td>{{ $item['nama'] }}</td>
                                <td>{{ $item['jabatan'] ?? '-' }}</td>
                                <td>{{ $item['satpelkes_nama'] ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}</td>
                                <td>{{ $item['hari'] ?? '-' }}</td>
                                <td>{{ $item['jam_masuk'] ?? '-' }}</td>
                                <td>{{ $item['jam_keluar'] ?? '-' }}</td>
                                <td>{{ $item['keterangan'] ?? 'Tidak Masuk' }}</td>
                                <td>
                                    @if(str_contains(strtolower($item['status_keterangan'] ?? ''), 'tugas luar'))
                                        Tugas Luar
                                    @elseif(str_contains(strtolower($item['status_keterangan'] ?? ''), 'izin'))
                                        {{ $item['status_keterangan'] }}
                                    @elseif(str_contains(strtolower($item['status_keterangan'] ?? ''), 'cuti'))
                                        {{ $item['status_keterangan'] }}
                                    @elseif(str_contains(strtolower($item['status_keterangan'] ?? ''), 'sakit'))
                                        {{ $item['status_keterangan'] }}
                                    @else
                                        Tidak Masuk
                                    @endif
                                </td>
                                <td class="no-print">
                                    @if(!isset($item['izin_cuti_id']) && $item['status_keterangan'] === 'Tidak Masuk')
                                        <a href="{{ route('izin-cuti.create', ['pegawai_id' => $item['pegawai_id'] ?? null, 'tanggal' => $item['tanggal']]) }}" 
                                           class="btn btn-sm btn-primary" title="Tambah Izin/Cuti/Sakit">
                                            <i class="fas fa-plus"></i> Izin/Cuti/Sakit
                                        </a>
                                    @elseif(isset($item['izin_cuti_id']))
                                        @if($item['izin_cuti_status'] === 'pending')
                                            <button type="button" class="btn btn-sm btn-success btn-approve-izin" 
                                                    data-izin-id="{{ $item['izin_cuti_id'] }}" title="Setujui">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-reject-izin" 
                                                    data-izin-id="{{ $item['izin_cuti_id'] }}" title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('izin-cuti.edit', $item['izin_cuti_id']) }}" 
                                           class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="print-footer">
                        <tr>
                            <td colspan="11" style="border-top: 2px solid #000; padding: 10px 0;">
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
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p>Semua pegawai hadir pada periode ini</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Approve -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Izin/Cuti/Sakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Masukkan keterangan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Izin/Cuti/Sakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_penolakan" class="form-control" rows="3" required placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
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
                content: "Sistem Absensi PPKP - Laporan Tidak Masuk";
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
// Handle approve button clicks
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-approve-izin').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-izin-id');
            document.getElementById('approveForm').action = '{{ url("/izin-cuti") }}/' + id + '/approve';
            var modal = new bootstrap.Modal(document.getElementById('approveModal'));
            modal.show();
        });
    });

    // Handle reject button clicks
    document.querySelectorAll('.btn-reject-izin').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-izin-id');
            document.getElementById('rejectForm').action = '{{ url("/izin-cuti") }}/' + id + '/reject';
            var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        });
    });
});

// Update page number saat print
window.addEventListener('beforeprint', function() {
    const pageNumbers = document.querySelectorAll('.page-number');
    pageNumbers.forEach(function(el) {
        el.textContent = '1'; // Akan diupdate oleh browser saat print
    });
});
</script>
@endsection


