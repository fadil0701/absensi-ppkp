@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chart-bar me-2"></i>Laporan</h2>
</div>

<!-- Form Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('laporan.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" 
                       value="{{ request('tanggal_mulai', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control form-control-sm" 
                       value="{{ request('tanggal_selesai', now()->endOfMonth()->format('Y-m-d')) }}">
            </div>
            @if(auth('web')->user()->role !== 'pegawai')
                <div class="col-md-3">
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
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Data -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Tanggal</th>
                        <th>Pegawai</th>
                        <th>Jenis</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensi as $index => $item)
                        <tr>
                            <td>{{ ($presensi->currentPage() - 1) * $presensi->perPage() + $index + 1 }}</td>
                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td>
                                <div>
                                    <strong>{{ $item->pegawai->nama ?? '-' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $item->pegawai->nip ?? '-' }}</small>
                                </div>
                            </td>
                            <td>
                                @if($item->jenis === 'check_in')
                                    <span class="badge bg-success">Masuk</span>
                                @else
                                    <span class="badge bg-info">Pulang</span>
                                @endif
                            </td>
                            <td>{{ $item->waktu_absen->format('H:i') }}</td>
                            <td>
                                @if($item->status === 'IN_ZONE')
                                    <span class="badge bg-success">IN ZONE</span>
                                @elseif($item->status === 'OUT_ZONE_PENDING')
                                    <span class="badge bg-warning">PENDING</span>
                                @elseif($item->status === 'APPROVED')
                                    <span class="badge bg-primary">APPROVED</span>
                                @elseif($item->status === 'REJECTED')
                                    <span class="badge bg-danger">REJECTED</span>
                                @else
                                    <span class="badge bg-secondary">{{ $item->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('presensi.show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">Tidak ada data presensi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($presensi->total() > 0)
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan {{ $presensi->firstItem() ?? 0 }}-{{ $presensi->lastItem() ?? 0 }} dari {{ $presensi->total() }} data
                </small>
                <div>
                    {{ $presensi->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Export Options -->
<div class="row mt-4">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3">Laporan Akumulasi Bulanan</h6>
                <p class="card-text small text-muted mb-3">Lihat akumulasi absensi, keterlambatan, dan pulang cepat per bulan</p>
                <a href="{{ route('laporan.akumulasi') }}" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-chart-line me-1"></i>Lihat Akumulasi
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3">Export Excel/PDF</h6>
                <form action="{{ route('laporan.export-excel') }}" method="GET" id="exportForm">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="date" name="tanggal_mulai" class="form-control form-control-sm" 
                                   value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <input type="date" name="tanggal_selesai" class="form-control form-control-sm" 
                                   value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-sm" name="format" value="excel">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </button>
                        <a href="#" class="btn btn-danger btn-sm" onclick="exportPdf(event)">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3">Laporan Telat</h6>
                <form action="{{ route('laporan.telat') }}" method="GET">
                    <div class="mb-2">
                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm" 
                               value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <input type="date" name="tanggal_selesai" class="form-control form-control-sm" 
                               value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search me-1"></i>Lihat
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3">Tidak Masuk</h6>
                <form action="{{ route('laporan.tidakMasuk') }}" method="GET">
                    <div class="mb-2">
                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm" 
                               value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <input type="date" name="tanggal_selesai" class="form-control form-control-sm" 
                               value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search me-1"></i>Lihat
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function exportPdf(event) {
        event.preventDefault();
        const form = document.getElementById('exportForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        window.location.href = '{{ route("laporan.export-pdf") }}?' + params.toString();
    }
</script>
@endpush
@endsection
