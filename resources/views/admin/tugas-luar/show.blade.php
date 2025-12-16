@extends('layouts.app')

@section('title', 'Detail Tugas Luar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-briefcase me-2"></i>Detail Tugas Luar</h2>
    <div>
        <a href="{{ route('tugas-luar.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        @if($tugasLuar->status === 'pending' && ($tugasLuar->pegawai_id === auth('web')->id() || auth('web')->user()->role !== 'pegawai'))
            <a href="{{ route('tugas-luar.edit', $tugasLuar) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Tugas Luar</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Pegawai</th>
                        <td>
                            <strong>{{ $tugasLuar->pegawai->nama }}</strong><br>
                            <small class="text-muted">{{ $tugasLuar->pegawai->nip }}</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Tanggal Mulai</th>
                        <td>{{ $tugasLuar->tanggal_mulai->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Selesai</th>
                        <td>{{ $tugasLuar->tanggal_selesai->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>Durasi</th>
                        <td>{{ $tugasLuar->tanggal_mulai->diffInDays($tugasLuar->tanggal_selesai) + 1 }} hari</td>
                    </tr>
                    <tr>
                        <th>Lokasi Tugas</th>
                        <td>{{ $tugasLuar->lokasi_tugas }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($tugasLuar->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($tugasLuar->status === 'disetujui')
                                <span class="badge bg-success">Disetujui</span>
                            @else
                                <span class="badge bg-danger">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    @if($tugasLuar->disetujuiOleh)
                        <tr>
                            <th>Disetujui Oleh</th>
                            <td>
                                {{ $tugasLuar->disetujuiOleh->nama }}<br>
                                <small class="text-muted">{{ $tugasLuar->waktu_persetujuan ? $tugasLuar->waktu_persetujuan->format('d F Y H:i') : '' }}</small>
                            </td>
                        </tr>
                    @endif
                    @if($tugasLuar->keterangan)
                        <tr>
                            <th>Keterangan</th>
                            <td>{{ $tugasLuar->keterangan }}</td>
                        </tr>
                    @endif
                    @if($tugasLuar->dokumen)
                        <tr>
                            <th>Dokumen Bukti</th>
                            <td>
                                <a href="{{ asset('storage/' . $tugasLuar->dokumen) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-file-pdf me-1"></i>Lihat Dokumen
                                </a>
                                <small class="text-muted d-block mt-1">
                                    {{ basename($tugasLuar->dokumen) }}
                                </small>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        @if($tugasLuar->status === 'pending' && (auth('web')->user()->role === 'admin' || auth('web')->user()->role === 'pimpinan'))
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tugas-luar.approve', $tugasLuar) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Setujui tugas luar ini?')">
                            <i class="fas fa-check me-1"></i>Setujui
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times me-1"></i>Tolak
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
@if($tugasLuar->status === 'pending' && (auth('web')->user()->role === 'admin' || auth('web')->user()->role === 'pimpinan'))
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tugas-luar.reject', $tugasLuar) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Tugas Luar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Keterangan Penolakan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Masukkan alasan penolakan..." required></textarea>
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
@endif
@endsection

