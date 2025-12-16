@extends('layouts.app')

@section('title', 'Data Presensi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-clock me-2"></i>Data Presensi</h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('presensi.index') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Cari NIP/Nama..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}" placeholder="Dari">
                </div>
                <div class="col-md-2">
                    <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}" placeholder="Sampai">
                </div>
                <div class="col-md-2">
                    <select name="jenis" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="check_in" {{ request('jenis') == 'check_in' ? 'selected' : '' }}>Absen Masuk</option>
                        <option value="check_out" {{ request('jenis') == 'check_out' ? 'selected' : '' }}>Absen Pulang</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="tugas_luar" class="form-select">
                        <option value="">Semua Presensi</option>
                        <option value="1" {{ request('tugas_luar') == '1' ? 'selected' : '' }}>Tugas Luar</option>
                        <option value="0" {{ request('tugas_luar') == '0' ? 'selected' : '' }}>Rutin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="IN_ZONE" {{ request('status') == 'IN_ZONE' ? 'selected' : '' }}>IN ZONE</option>
                        <option value="OUT_ZONE_PENDING" {{ request('status') == 'OUT_ZONE_PENDING' ? 'selected' : '' }}>PENDING</option>
                        <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                        <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Cari
                    </button>
                    <a href="{{ route('presensi.index') }}" class="btn btn-secondary">Reset</a>
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
                        <th>Unit Kerja</th>
                        <th>Jarak</th>
                        <th>Status</th>
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
                                    <span class="badge bg-success">Absen Masuk</span>
                                @else
                                    <span class="badge bg-info">Absen Pulang</span>
                                @endif
                            </td>
                            <td>{{ $p->waktu_absen->format('H:i:s') }}</td>
                            <td>{{ $p->satpelkes->nama_satpelkes ?? '-' }}</td>
                            <td>
                                {{ $p->jarak_ke_satpelkes ? number_format($p->jarak_ke_satpelkes, 2) . ' m' : '-' }}
                                @if($p->keterangan && stripos($p->keterangan, 'tugas luar') !== false)
                                    <br><small class="badge bg-info">Tugas Luar</small>
                                @endif
                            </td>
                            <td>
                                @if($p->status === 'IN_ZONE')
                                    <span class="badge bg-success">IN ZONE</span>
                                @elseif($p->status === 'OUT_ZONE_PENDING')
                                    <span class="badge bg-warning">PENDING</span>
                                @elseif($p->status === 'APPROVED')
                                    <span class="badge bg-primary">APPROVED</span>
                                @elseif($p->status === 'REJECTED')
                                    <span class="badge bg-danger">REJECTED</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('presensi.show', $p->id) }}" class="btn btn-sm btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Tidak ada data presensi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($presensi->hasPages())
            <div class="mt-4">
                <x-pagination :paginator="$presensi" />
            </div>
        @endif
    </div>
</div>
@endsection

