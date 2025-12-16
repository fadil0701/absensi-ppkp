@extends('layouts.app')

@section('title', 'Tugas Luar Pending')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-clock me-2"></i>Tugas Luar Pending Approval</h2>
    <a href="{{ route('tugas-luar.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Tanggal</th>
                        <th>Lokasi Tugas</th>
                        <th>Keterangan</th>
                        <th>Diajukan</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tugasLuar as $tl)
                        <tr>
                            <td>
                                <strong>{{ $tl->pegawai->nama }}</strong><br>
                                <small class="text-muted">{{ $tl->pegawai->nip }}</small>
                            </td>
                            <td>
                                {{ $tl->tanggal_mulai->format('d/m/Y') }}<br>
                                <small class="text-muted">s/d {{ $tl->tanggal_selesai->format('d/m/Y') }}</small>
                            </td>
                            <td>{{ $tl->lokasi_tugas }}</td>
                            <td>{{ $tl->keterangan ? \Illuminate\Support\Str::limit($tl->keterangan, 50) : '-' }}</td>
                            <td>
                                {{ $tl->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <a href="{{ route('tugas-luar.show', $tl) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <form action="{{ route('tugas-luar.approve', $tl) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui tugas luar ini?')">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $tl->id }}">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </td>
                        </tr>

                        <!-- Reject Modal -->
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
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada tugas luar yang pending</td>
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

