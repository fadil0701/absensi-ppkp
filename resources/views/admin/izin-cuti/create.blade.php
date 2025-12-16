@extends('layouts.app')

@section('title', 'Tambah Izin/Cuti')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus me-2"></i>Tambah Izin/Cuti</h2>
    <a href="{{ route('laporan.tidakMasuk', ['tanggal_mulai' => $tanggal->format('Y-m-d'), 'tanggal_selesai' => $tanggal->format('Y-m-d')]) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

@if($existing)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Sudah ada izin/cuti/sakit untuk pegawai ini pada tanggal {{ $tanggal->format('d/m/Y') }}.
        <a href="{{ route('izin-cuti.edit', $existing->id) }}" class="alert-link">Edit Izin/Cuti/Sakit</a>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('izin-cuti.store') }}" method="POST">
            @csrf
            
            <input type="hidden" name="pegawai_id" value="{{ $pegawai->id }}">
            <input type="hidden" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}">

            <div class="mb-3">
                <label class="form-label">Pegawai</label>
                <input type="text" class="form-control" value="{{ $pegawai->nama }} ({{ $pegawai->nip }})" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="text" class="form-control" value="{{ $tanggal->format('d/m/Y') }} ({{ $tanggal->locale('id')->isoFormat('dddd') }})" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Jenis <span class="text-danger">*</span></label>
                <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                    <option value="">Pilih Jenis</option>
                    <option value="izin" {{ old('jenis') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="cuti" {{ old('jenis') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                    <option value="sakit" {{ old('jenis') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
                @error('jenis')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                          rows="3" placeholder="Masukkan keterangan izin/cuti...">{{ old('keterangan') }}</textarea>
                @error('keterangan')
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

