@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chart-bar me-2"></i>Laporan</h2>
</div>

<div class="row">
    <!-- Laporan Semua Presensi dengan Export -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-export me-2"></i>Laporan Semua Presensi</h5>
            </div>
            <div class="card-body">
                <p class="card-text">Export laporan lengkap semua presensi dalam periode tertentu ke PDF atau Excel</p>
                <form action="{{ route('laporan.export-excel') }}" method="GET" id="exportForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        @if(auth('web')->user()->role !== 'pegawai')
                            <div class="col-md-3">
                                <label class="form-label">Pegawai (Optional)</label>
                                <select name="pegawai_id" class="form-select">
                                    <option value="">Semua Pegawai</option>
                                    @foreach(\App\Models\Pegawai::aktif()->orderBy('nama')->get() as $pegawai)
                                        <option value="{{ $pegawai->id }}">{{ $pegawai->nama }} ({{ $pegawai->nip }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label">Jenis Absen (Optional)</label>
                            <select name="jenis_absen" class="form-select">
                                <option value="">Semua Jenis</option>
                                <option value="Rutin">Rutin</option>
                                <option value="Tidak Masuk">Tidak Masuk</option>
                                <option value="Izin">Izin</option>
                                <option value="Cuti">Cuti</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Tugas Luar">Tugas Luar</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success" name="format" value="excel">
                                    <i class="fas fa-file-excel me-1"></i>Export Excel
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <a href="#" class="btn btn-danger" onclick="exportPdf(event)">
                                    <i class="fas fa-file-pdf me-1"></i>Export PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-clock me-2"></i>Laporan Keterlambatan</h5>
                <p class="card-text">Lihat laporan pegawai yang terlambat dalam periode tertentu</p>
                <form action="{{ route('laporan.telat') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    @if(auth('web')->user()->role !== 'pegawai')
                        <div class="mb-3">
                            <label class="form-label">Pegawai (Optional)</label>
                            <select name="pegawai_id" class="form-select">
                                <option value="">Semua Pegawai</option>
                                @foreach(\App\Models\Pegawai::aktif()->orderBy('nama')->get() as $pegawai)
                                    <option value="{{ $pegawai->id }}">{{ $pegawai->nama }} ({{ $pegawai->nip }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Generate Laporan
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user-times me-2"></i>Laporan Tidak Masuk</h5>
                <p class="card-text">Lihat laporan pegawai yang tidak masuk dalam periode tertentu</p>
                <form action="{{ route('laporan.tidakMasuk') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    @if(auth('web')->user()->role !== 'pegawai')
                        <div class="mb-3">
                            <label class="form-label">Pegawai (Optional)</label>
                            <select name="pegawai_id" class="form-select">
                                <option value="">Semua Pegawai</option>
                                @foreach(\App\Models\Pegawai::aktif()->orderBy('nama')->get() as $pegawai)
                                    <option value="{{ $pegawai->id }}">{{ $pegawai->nama }} ({{ $pegawai->nip }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Generate Laporan
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
        
        // Buat URL untuk export PDF
        const params = new URLSearchParams(formData);
        window.location.href = '{{ route("laporan.export-pdf") }}?' + params.toString();
    }
</script>
@endpush
@endsection

