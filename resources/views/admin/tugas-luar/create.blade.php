@extends('layouts.app')

@section('title', 'Tambah Tugas Luar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus me-2"></i>Tambah Tugas Luar</h2>
    <a href="{{ route('tugas-luar.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('tugas-luar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            @if(auth('web')->user()->role !== 'pegawai')
                <div class="mb-3">
                    <label class="form-label">Pegawai <span class="text-danger">*</span></label>
                    <select name="pegawai_id" class="form-select @error('pegawai_id') is-invalid @enderror" required>
                        <option value="">Pilih Pegawai</option>
                        @foreach($pegawaiList as $pegawai)
                            <option value="{{ $pegawai->id }}" {{ old('pegawai_id') == $pegawai->id ? 'selected' : '' }}>
                                {{ $pegawai->nama }} ({{ $pegawai->nip }})
                            </option>
                        @endforeach
                    </select>
                    @error('pegawai_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @else
                <input type="hidden" name="pegawai_id" value="{{ auth('web')->id() }}">
                <div class="mb-3">
                    <label class="form-label">Pegawai</label>
                    <input type="text" class="form-control" value="{{ auth('web')->user()->nama }} ({{ auth('web')->user()->nip }})" disabled>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                               value="{{ old('tanggal_mulai') }}" required>
                        @error('tanggal_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                               value="{{ old('tanggal_selesai') }}" required>
                        @error('tanggal_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Lokasi Tugas <span class="text-danger">*</span></label>
                <input type="text" name="lokasi_tugas" class="form-control @error('lokasi_tugas') is-invalid @enderror" 
                       value="{{ old('lokasi_tugas') }}" placeholder="Masukkan lokasi tugas luar" required>
                @error('lokasi_tugas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                          rows="3" placeholder="Masukkan keterangan tugas luar...">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Dokumen Bukti Surat Tugas</label>
                <input type="file" name="dokumen" class="form-control @error('dokumen') is-invalid @enderror" 
                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Format yang diizinkan: PDF, JPG, PNG, DOC, DOCX. Maksimal 10MB.
                </small>
                @error('dokumen')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

