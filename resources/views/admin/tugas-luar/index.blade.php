@extends('layouts.app')

@section('title', 'Tugas Luar')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <h2 class="mb-2 mb-md-0"><i class="fas fa-briefcase me-2"></i>Tugas Luar</h2>
</div>

<div class="mb-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-1">
    @if(auth('web')->user()->role === 'admin' || auth('web')->user()->role === 'pimpinan')
        <div class="mb-3">
            <a href="{{ route('tugas-luar.pending') }}" class="btn btn-warning">
                <i class="fas fa-clock me-1"></i>Pending Approval
            </a>
        </div>
    @endif

    <a href="{{ route('tugas-luar.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Tugas Luar
    </a>
</div>
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-2 g-md-3">
            @if(auth('web')->user()->role === 'admin' || auth('web')->user()->role === 'pimpinan')
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari pegawai..." value="{{ request('search') }}">
                </div>
            @endif
            <div class="col-6 {{ auth('web')->user()->role === 'pegawai' ? 'col-md-4' : 'col-md-3' }}">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-6 {{ auth('web')->user()->role === 'pegawai' ? 'col-md-4' : 'col-md-2' }}">
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}" placeholder="Dari">
            </div>
            <div class="col-6 {{ auth('web')->user()->role === 'pegawai' ? 'col-md-4' : 'col-md-2' }}">
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}" placeholder="Sampai">
            </div>
            <div class="col-6 {{ auth('web')->user()->role === 'pegawai' ? 'col-md-12' : 'col-md-1' }}">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search d-md-none"></i>
                    <span class="d-none d-md-inline">Cari</span>
                </button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="d-none d-md-table-header-group">
                    <tr>
                        <th>Pegawai</th>
                        <th>Tanggal</th>
                        <th>Lokasi Tugas</th>
                        <th>Status</th>
                        <th>Disetujui Oleh</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tugasLuar as $tl)
                        <tr>
                            <td data-label="Pegawai">
                                <strong>{{ $tl->pegawai->nama }}</strong><br>
                                <small class="text-muted">{{ $tl->pegawai->nip }}</small>
                            </td>
                            <td data-label="Tanggal">
                                {{ $tl->tanggal_mulai->format('d/m/Y') }}<br>
                                <small class="text-muted">s/d {{ $tl->tanggal_selesai->format('d/m/Y') }}</small>
                            </td>
                            <td data-label="Lokasi Tugas">{{ $tl->lokasi_tugas }}</td>
                            <td data-label="Status">
                                @if($tl->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($tl->status === 'disetujui')
                                    <span class="badge bg-success">Disetujui</span>
                                @else
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>
                            <td data-label="Disetujui Oleh" class="d-none d-md-table-cell">
                                @if($tl->disetujuiOleh)
                                    {{ $tl->disetujuiOleh->nama }}<br>
                                    <small class="text-muted">{{ $tl->waktu_persetujuan ? $tl->waktu_persetujuan->format('d/m/Y H:i') : '' }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td data-label="Actions">
                                <div class="btn-group-vertical btn-group-sm d-md-none w-100" role="group">
                                    <a href="{{ route('tugas-luar.show', $tl) }}" class="btn btn-info mb-1">
                                        <i class="fas fa-eye me-1"></i>Detail
                                    </a>
                                    @if($tl->status === 'pending' && ($tl->pegawai_id === auth('web')->id() || auth('web')->user()->role !== 'pegawai'))
                                        <a href="{{ route('tugas-luar.edit', $tl) }}" class="btn btn-warning mb-1">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <form action="{{ route('tugas-luar.destroy', $tl) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="fas fa-trash me-1"></i>Hapus
                                            </button>
                                        </form>
                                    @endif
                                    @if($tl->status === 'pending' && (auth('web')->user()->role === 'admin' || auth('web')->user()->role === 'pimpinan'))
                                        <form action="{{ route('tugas-luar.approve', $tl) }}" method="POST" class="mb-1">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Setujui tugas luar ini?')">
                                                <i class="fas fa-check me-1"></i>Setujui
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $tl->id }}">
                                            <i class="fas fa-times me-1"></i>Tolak
                                        </button>
                                    @endif
                                </div>
                                <div class="d-none d-md-block">
                                    <a href="{{ route('tugas-luar.show', $tl) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($tl->status === 'pending' && ($tl->pegawai_id === auth('web')->id() || auth('web')->user()->role !== 'pegawai'))
                                        <a href="{{ route('tugas-luar.edit', $tl) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('tugas-luar.destroy', $tl) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($tl->status === 'pending' && (auth('web')->user()->role === 'admin' || auth('web')->user()->role === 'pimpinan'))
                                        <form action="{{ route('tugas-luar.approve', $tl) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui tugas luar ini?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $tl->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Reject Modal -->
                        @if($tl->status === 'pending' && (auth('web')->user()->role === 'admin' || auth('web')->user()->role === 'pimpinan'))
                            <div class="modal fade" id="rejectModal{{ $tl->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('tugas-luar.reject', $tl) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tolak Tugas Luar</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Keterangan Penolakan</label>
                                                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
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
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data tugas luar</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
        @if($tugasLuar->hasPages())
            <div class="mt-4">
                <x-pagination :paginator="$tugasLuar" />
            </div>
        @endif
        </div>
    </div>
</div>
@endsection

