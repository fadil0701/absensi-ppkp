@extends('layouts.app')

@section('title', 'Edit Jadwal Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Jadwal Pegawai</h2>
    <a href="{{ route('jadwal-pegawai.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('jadwal-pegawai.update', $jadwal) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label class="form-label">Pegawai <span class="text-danger">*</span></label>
                <select name="pegawai_id" class="form-select @error('pegawai_id') is-invalid @enderror" required>
                    <option value="">Pilih Pegawai</option>
                    @foreach($pegawaiList as $pegawai)
                        <option value="{{ $pegawai->id }}" {{ old('pegawai_id', $jadwal->pegawai_id) == $pegawai->id ? 'selected' : '' }}>
                            {{ $pegawai->nama }} ({{ $pegawai->nip }})
                        </option>
                    @endforeach
                </select>
                @error('pegawai_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>


            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Hari (Opsional)</label>
                        <select name="hari" class="form-select @error('hari') is-invalid @enderror">
                            <option value="">Setiap Hari</option>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                                <option value="{{ $hari }}" {{ old('hari', $jadwal->hari) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                            @endforeach
                        </select>
                        @error('hari')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                        <input type="time" name="jam_masuk" id="jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror" 
                               value="{{ old('jam_masuk', is_string($jadwal->jam_masuk) ? substr($jadwal->jam_masuk, 0, 5) : \Carbon\Carbon::parse($jadwal->jam_masuk)->format('H:i')) }}" required>
                        @error('jam_masuk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Jam Keluar <span class="text-danger">*</span></label>
                        <input type="time" name="jam_keluar" id="jam_keluar" class="form-control @error('jam_keluar') is-invalid @enderror" 
                               value="{{ old('jam_keluar', is_string($jadwal->jam_keluar) ? substr($jadwal->jam_keluar, 0, 5) : \Carbon\Carbon::parse($jadwal->jam_keluar)->format('H:i')) }}" required>
                        @error('jam_keluar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Toleransi Telat (menit)</label>
                        <input type="number" name="toleransi_telat" id="toleransi" class="form-control @error('toleransi_telat') is-invalid @enderror" 
                               value="{{ old('toleransi_telat', $jadwal->toleransi_telat) }}" min="0" max="60">
                        @error('toleransi_telat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai (Opsional)</label>
                        <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                               value="{{ old('tanggal_mulai', $jadwal->tanggal_mulai ? $jadwal->tanggal_mulai->format('Y-m-d') : '') }}">
                        @error('tanggal_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai (Opsional)</label>
                        <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                               value="{{ old('tanggal_selesai', $jadwal->tanggal_selesai ? $jadwal->tanggal_selesai->format('Y-m-d') : '') }}">
                        @error('tanggal_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="is_aktif" {{ old('is_aktif', $jadwal->is_aktif) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_aktif">
                        Aktif
                    </label>
                </div>
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

