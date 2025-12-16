@extends('layouts.app')

@section('title', 'Edit Tugas Luar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Tugas Luar</h2>
    <a href="{{ route('tugas-luar.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('tugas-luar.update', $tugasLuar) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            @if(auth('web')->user()->role !== 'pegawai')
                <div class="mb-3">
                    <label class="form-label">Pegawai <span class="text-danger">*</span></label>
                    <select name="pegawai_id" class="form-select @error('pegawai_id') is-invalid @enderror" required>
                        <option value="">Pilih Pegawai</option>
                        @foreach($pegawaiList as $pegawai)
                            <option value="{{ $pegawai->id }}" {{ (old('pegawai_id', $tugasLuar->pegawai_id) == $pegawai->id) ? 'selected' : '' }}>
                                {{ $pegawai->nama }} ({{ $pegawai->nip }})
                            </option>
                        @endforeach
                    </select>
                    @error('pegawai_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @else
                <div class="mb-3">
                    <label class="form-label">Pegawai</label>
                    <input type="text" class="form-control" value="{{ $tugasLuar->pegawai->nama }} ({{ $tugasLuar->pegawai->nip }})" disabled>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                               value="{{ old('tanggal_mulai', $tugasLuar->tanggal_mulai->format('Y-m-d')) }}" required>
                        @error('tanggal_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                               value="{{ old('tanggal_selesai', $tugasLuar->tanggal_selesai->format('Y-m-d')) }}" required>
                        @error('tanggal_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Lokasi Tugas <span class="text-danger">*</span></label>
                <input type="text" name="lokasi_tugas" class="form-control @error('lokasi_tugas') is-invalid @enderror" 
                       value="{{ old('lokasi_tugas', $tugasLuar->lokasi_tugas) }}" placeholder="Masukkan lokasi tugas luar" required>
                @error('lokasi_tugas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                          rows="3" placeholder="Masukkan keterangan tugas luar...">{{ old('keterangan', $tugasLuar->keterangan) }}</textarea>
                @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Dokumen Bukti Surat Tugas</label>
                @if($tugasLuar->dokumen)
                    <div class="mb-2">
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file me-2"></i>
                                <a href="{{ asset('storage/' . $tugasLuar->dokumen) }}" target="_blank" class="text-decoration-none">
                                    Lihat Dokumen Terlampir
                                </a>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="hapus_dokumen" id="hapus_dokumen" value="1">
                                <label class="form-check-label" for="hapus_dokumen">
                                    Hapus Dokumen
                                </label>
                            </div>
                        </div>
                    </div>
                @endif
                <input type="file" name="dokumen" class="form-control @error('dokumen') is-invalid @enderror" 
                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Format yang diizinkan: PDF, JPG, PNG, DOC, DOCX. Maksimal 10MB.
                    @if($tugasLuar->dokumen)
                        Upload file baru untuk mengganti dokumen yang sudah ada.
                    @endif
                </small>
                @error('dokumen')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

