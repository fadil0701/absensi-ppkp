@extends('layouts.app')

@section('title', 'Jadwal Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-alt me-2"></i>Jadwal Pegawai</h2>
    <div>
        <a href="{{ route('jadwal-pegawai.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Tambah Jadwal
        </a>
        <a href="{{ route('jadwal-pegawai.create') }}?bulk=1" class="btn btn-success">
            <i class="fas fa-calendar-plus me-1"></i>Buat Jadwal Bulk
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari pegawai..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="pegawai_id" class="form-select">
                    <option value="">Semua Pegawai</option>
                    @foreach($pegawaiList as $pegawaiItem)
                        <option value="{{ $pegawaiItem->id }}" {{ request('pegawai_id') == $pegawaiItem->id ? 'selected' : '' }}>
                            {{ $pegawaiItem->nama }} ({{ $pegawaiItem->nip }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_aktif" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('is_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('is_aktif') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-warning w-100">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('jadwal-pegawai.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>NIP</th>
                        <th>Divisi</th>
                        <th>Jabatan</th>
                        <th>Satpelkes</th>
                        <th>Total Jadwal Aktif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawai as $p)
                        <tr>
                            <td>
                                <strong>{{ $p->nama ?? '-' }}</strong>
                            </td>
                            <td>{{ $p->nip ?? '-' }}</td>
                            <td>{{ $p->divisi ?? '-' }}</td>
                            <td>{{ $p->jabatan ?? '-' }}</td>
                            <td>
                                @if($p->satpelkes)
                                    {{ $p->satpelkes->nama_satpelkes }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $p->total_jadwal ?? 0 }} jadwal</span>
                            </td>
                            <td>
                                <a href="{{ route('jadwal-pegawai.show', $p->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-calendar-alt me-1"></i>Lihat Jadwal
                                </a>
                                <a href="{{ route('jadwal-pegawai.create', ['pegawai_id' => $p->id]) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus me-1"></i>Tambah Jadwal
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Tidak ada pegawai yang memiliki jadwal.
                                    <a href="{{ route('jadwal-pegawai.create') }}" class="alert-link ms-2">Tambah jadwal sekarang</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
        @if($pegawai->hasPages())
            <div class="mt-4">
                <x-pagination :paginator="$pegawai" />
            </div>
        @endif
        </div>
    </div>
</div>
@endsection

