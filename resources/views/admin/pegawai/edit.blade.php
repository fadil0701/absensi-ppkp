@extends('layouts.app')

@section('title', 'Edit Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Pegawai</h2>
    <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('pegawai.update', $pegawai) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $pegawai->nip) }}" required>
                        @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $pegawai->nama) }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $pegawai->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Rumpun Jabatan</label>
                        <select name="divisi" class="form-select">
                            <option value="">Pilih Rumpun Jabatan</option>
                            <option value="Struktural" {{ old('divisi', $pegawai->divisi) == 'Struktural' ? 'selected' : '' }}>Struktural</option>
                            <option value="Jabatan Pelaksana" {{ old('divisi', $pegawai->divisi) == 'Jabatan Pelaksana' ? 'selected' : '' }}>Jabatan Pelaksana</option>
                            <option value="Jabatan Fungsional" {{ old('divisi', $pegawai->divisi) == 'Jabatan Fungsional' ? 'selected' : '' }}>Jabatan Fungsional</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $pegawai->jabatan) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <select name="satpelkes_id" class="form-select">
                            <option value="">Pilih Unit</option>
                            @foreach($satpelkes as $s)
                                <option value="{{ $s->id }}" {{ old('satpelkes_id', $pegawai->satpelkes_id) == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama_satpelkes }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="pegawai" {{ old('role', $pegawai->role) == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                            <option value="pimpinan" {{ old('role', $pegawai->role) == 'pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                            <option value="admin" {{ old('role', $pegawai->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="aktif" {{ old('status', $pegawai->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status', $pegawai->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update
                </button>
                <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection


