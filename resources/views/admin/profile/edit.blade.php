@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Profile</h2>
    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">NIP</label>
                        <input type="text" class="form-control" value="{{ $pegawai->nip }}" disabled>
                        <small class="text-muted">NIP tidak dapat diubah</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="{{ ucfirst($pegawai->role) }}" disabled>
                        <small class="text-muted">Role tidak dapat diubah</small>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" 
                       value="{{ old('nama', $pegawai->nama) }}" required>
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                       value="{{ old('email', $pegawai->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Rumpun Jabatan</label>
                        <select name="divisi" class="form-select @error('divisi') is-invalid @enderror">
                            <option value="">Pilih Rumpun Jabatan</option>
                            <option value="Struktural" {{ old('divisi', $pegawai->divisi) == 'Struktural' ? 'selected' : '' }}>Struktural</option>
                            <option value="Jabatan Pelaksana" {{ old('divisi', $pegawai->divisi) == 'Jabatan Pelaksana' ? 'selected' : '' }}>Jabatan Pelaksana</option>
                            <option value="Jabatan Fungsional" {{ old('divisi', $pegawai->divisi) == 'Jabatan Fungsional' ? 'selected' : '' }}>Jabatan Fungsional</option>
                        </select>
                        @error('divisi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror" 
                               value="{{ old('jabatan', $pegawai->jabatan) }}">
                        @error('jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @if(auth('web')->user()->role === 'admin')
                <div class="mb-3">
                    <label class="form-label">Satpelkes</label>
                    <select name="satpelkes_id" class="form-select @error('satpelkes_id') is-invalid @enderror">
                        <option value="">Pilih Satpelkes</option>
                        @foreach($satpelkesList as $satpelkes)
                            <option value="{{ $satpelkes->id }}" {{ old('satpelkes_id', $pegawai->satpelkes_id) == $satpelkes->id ? 'selected' : '' }}>
                                {{ $satpelkes->nama_satpelkes }}
                            </option>
                        @endforeach
                    </select>
                    @error('satpelkes_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @else
                <div class="mb-3">
                    <label class="form-label">Satpelkes</label>
                    <input type="text" class="form-control" value="{{ $pegawai->satpelkes->nama_satpelkes ?? '-' }}" disabled>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label">Foto</label>
                @if($pegawai->foto)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $pegawai->foto) }}" alt="{{ $pegawai->nama }}" 
                             class="img-thumbnail img-profile-edit">
                    </div>
                @endif
                <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" 
                       accept="image/jpeg,image/png,image/jpg">
                <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                @error('foto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr>

            <h5 class="mb-3">Ubah Password</h5>
            <p class="text-muted">Kosongkan jika tidak ingin mengubah password</p>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


