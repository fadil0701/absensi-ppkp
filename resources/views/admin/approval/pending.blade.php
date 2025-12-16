@extends('layouts.app')

@section('title', 'Pending Approval')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-check-circle me-2"></i>Pending Approval</h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('approval.pending') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari Nama Pegawai..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('approval.pending') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pegawai</th>
                        <th>Jenis</th>
                        <th>Waktu</th>
                        <th>Unit Terdekat</th>
                        <th>Jarak</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensi as $p)
                        <tr>
                            <td>{{ $p->tanggal->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $p->pegawai->nama }}</strong><br>
                                <small class="text-muted">{{ $p->pegawai->nip }}</small>
                            </td>
                            <td>
                                @if($p->jenis === 'check_in')
                                    <span class="badge bg-success">Check In</span>
                                @else
                                    <span class="badge bg-info">Check Out</span>
                                @endif
                            </td>
                            <td>{{ $p->waktu_absen->format('H:i:s') }}</td>
                            <td>
                                {{ $p->satpelkes->nama_satpelkes ?? '-' }}<br>
                                <small class="text-muted">Radius: {{ $p->satpelkes->radius_absensi ?? '-' }}m</small>
                            </td>
                            <td>
                                @if($p->jarak_ke_satpelkes)
                                    <span class="badge bg-warning">
                                        {{ number_format($p->jarak_ke_satpelkes, 2) }}m
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $p->keterangan ?? '-' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('presensi.show', $p->id) }}" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $p->id }}" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Approve Modal -->
                        <div class="modal fade" id="approveModal{{ $p->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('approval.approve', $p->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Approve Presensi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Yakin ingin approve presensi <strong>{{ $p->pegawai->nama }}</strong>?</p>
                                            <div class="mb-3">
                                                <label class="form-label">Catatan (Optional)</label>
                                                <textarea name="catatan" class="form-control" rows="3" placeholder="Masukkan catatan..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">Approve</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $p->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('approval.reject', $p->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Presensi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Yakin ingin reject presensi <strong>{{ $p->pegawai->nama }}</strong>?</p>
                                            <div class="mb-3">
                                                <label class="form-label">Catatan <span class="text-danger">*</span></label>
                                                <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3" placeholder="Masukkan alasan reject..." required></textarea>
                                                @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i><br>
                                Tidak ada presensi yang pending approval
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
        @if($presensi->hasPages())
            <div class="mt-4">
                <x-pagination :paginator="$presensi" />
            </div>
        @endif
        </div>
    </div>
</div>
@endsection


