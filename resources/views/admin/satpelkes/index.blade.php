@extends('layouts.app')

@section('title', 'Kelola Unit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-building me-2"></i>Kelola Unit</h2>
    <a href="{{ route('satpelkes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Unit
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('satpelkes.index') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari Nama/Kode Satpelkes..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="is_aktif" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('is_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('is_aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('satpelkes.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Unit</th>
                        <th>Koordinat</th>
                        <th>Radius</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($satpelkes as $s)
                        <tr>
                            <td><strong>{{ $s->kode_satpelkes }}</strong></td>
                            <td>{{ $s->nama_satpelkes }}</td>
                            <td>
                                <small>{{ $s->latitude }}, {{ $s->longitude }}</small>
                            </td>
                            <td>{{ $s->radius_absensi }}m</td>
                            <td>{{ $s->alamat ?? '-' }}</td>
                            <td>
                                @if($s->is_aktif)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('satpelkes.show', $s) }}" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('satpelkes.edit', $s) }}" class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('satpelkes.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Tidak ada data unit</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
        @if($satpelkes->hasPages())
            <div class="mt-4">
                <x-pagination :paginator="$satpelkes" />
            </div>
        @endif
        </div>
    </div>
</div>
@endsection


