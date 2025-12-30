@extends('layouts.app')

@section('title', 'Tambah API Key')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-key me-2"></i>Tambah API Key</h2>
    <a href="{{ route('api-keys.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('api-keys.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Nama Aplikasi/Sistem <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: Sistem HR, Sistem Payroll">
                @error('name')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi penggunaan API Key">{{ old('description') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Webhook URL</label>
                <input type="url" name="webhook_url" class="form-control" value="{{ old('webhook_url') }}" placeholder="https://example.com/webhook">
                <small class="text-muted">URL untuk menerima notifikasi real-time</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Allowed IPs</label>
                <input type="text" name="allowed_ips" class="form-control" value="{{ old('allowed_ips') }}" placeholder="192.168.1.1, 10.0.0.1">
                <small class="text-muted">Pisahkan dengan koma. Kosongkan untuk mengizinkan semua IP.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Scopes (Permissions)</label>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="scopes[]" value="presensi" id="scope_presensi">
                            <label class="form-check-label" for="scope_presensi">Presensi</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="scopes[]" value="laporan" id="scope_laporan">
                            <label class="form-check-label" for="scope_laporan">Laporan</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="scopes[]" value="jadwal" id="scope_jadwal">
                            <label class="form-check-label" for="scope_jadwal">Jadwal</label>
                        </div>
                    </div>
                </div>
                <small class="text-muted">Kosongkan untuk mengizinkan semua akses.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Rate Limit (per menit)</label>
                <input type="number" name="rate_limit" class="form-control" value="{{ old('rate_limit', 60) }}" min="1" max="1000">
                <small class="text-muted">Jumlah request maksimal per menit</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal Kadaluarsa</label>
                <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                <small class="text-muted">Kosongkan untuk tidak ada kadaluarsa</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Buat API Key
                </button>
                <a href="{{ route('api-keys.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
