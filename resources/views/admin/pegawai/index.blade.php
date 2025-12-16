@extends('layouts.app')

@section('title', 'Kelola Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Kelola Pegawai</h2>
    <a href="{{ route('pegawai.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Pegawai
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('pegawai.index') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari NIP/Nama/Email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="">Semua Role</option>
                        <option value="pegawai" {{ request('role') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                        <option value="pimpinan" {{ request('role') == 'pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('pegawai.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Rumpun Jabatan</th>
                        <th>Jabatan</th>
                        <th>Unit</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawai as $p)
                        <tr>
                            <td><strong>{{ $p->nip }}</strong></td>
                            <td>{{ $p->nama }}</td>
                            <td>{{ $p->email }}</td>
                            <td>{{ $p->divisi ?? '-' }}</td>
                            <td>{{ $p->jabatan ?? '-' }}</td>
                            <td>{{ $p->satpelkes->nama_satpelkes ?? '-' }}</td>
                            <td>
                                @if($p->role === 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @elseif($p->role === 'pimpinan')
                                    <span class="badge bg-warning">Pimpinan</span>
                                @else
                                    <span class="badge bg-primary">Pegawai</span>
                                @endif
                            </td>
                            <td>
                                @if($p->status === 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('pegawai.show', $p) }}" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('pegawai.edit', $p) }}" class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('pegawai.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?');">
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
                            <td colspan="9" class="text-center py-4">Tidak ada data pegawai</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pegawai->hasPages())
            <div class="mt-4">
                <x-pagination :paginator="$pegawai" />
            </div>
        @endif
    </div>
</div>
@endsection


