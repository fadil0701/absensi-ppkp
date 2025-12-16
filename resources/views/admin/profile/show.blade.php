@extends('layouts.app')

@section('title', 'Profile Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user me-2"></i>Profile Pegawai</h2>
    <div>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>Edit Profile
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($pegawai->foto)
                        <img src="{{ asset('storage/' . $pegawai->foto) }}" alt="{{ $pegawai->nama }}" 
                             class="img-thumbnail rounded-circle img-profile-thumbnail">
                    @else
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    @endif
                </div>
                <h4>{{ $pegawai->nama }}</h4>
                <p class="text-muted">{{ $pegawai->nip }}</p>
                @if($pegawai->role === 'admin')
                    <span class="badge bg-danger">Admin</span>
                @elseif($pegawai->role === 'pimpinan')
                    <span class="badge bg-warning">Pimpinan</span>
                @else
                    <span class="badge bg-primary">Pegawai</span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Pegawai</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">NIP</th>
                        <td><strong>{{ $pegawai->nip }}</strong></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td>{{ $pegawai->nama }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $pegawai->email }}</td>
                    </tr>
                    <tr>
                        <th>Divisi</th>
                        <td>{{ $pegawai->divisi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jabatan</th>
                        <td>{{ $pegawai->jabatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Satpelkes</th>
                        <td>{{ $pegawai->satpelkes->nama_satpelkes ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($pegawai->status === 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


