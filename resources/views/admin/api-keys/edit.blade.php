@extends('layouts.app')

@section('title', 'Edit API Key')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-key me-2"></i>Edit API Key</h2>
    <a href="{{ route('api-keys.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('api-keys.update', $apiKey->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label class="form-label">Nama Aplikasi/Sistem <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $apiKey->name) }}" required>
                @error('name')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $apiKey->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Webhook URL</label>
                <input type="url" name="webhook_url" class="form-control" value="{{ old('webhook_url', $apiKey->webhook_url) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Allowed IPs</label>
                <input type="text" name="allowed_ips" class="form-control" value="{{ old('allowed_ips', $apiKey->allowed_ips ? implode(', ', $apiKey->allowed_ips) : '') }}" placeholder="192.168.1.1, 10.0.0.1">
                <small class="text-muted">Pisahkan dengan koma. Kosongkan untuk mengizinkan semua IP.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Scopes (Permissions)</label>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="scopes[]" value="presensi" id="scope_presensi" {{ in_array('presensi', $apiKey->scopes ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="scope_presensi">Presensi</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="scopes[]" value="laporan" id="scope_laporan" {{ in_array('laporan', $apiKey->scopes ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="scope_laporan">Laporan</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="scopes[]" value="jadwal" id="scope_jadwal" {{ in_array('jadwal', $apiKey->scopes ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="scope_jadwal">Jadwal</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Rate Limit (per menit)</label>
                <input type="number" name="rate_limit" class="form-control" value="{{ old('rate_limit', $apiKey->rate_limit) }}" min="1" max="1000">
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal Kadaluarsa</label>
                <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', $apiKey->expires_at?->format('Y-m-d')) }}">
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $apiKey->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Aktif
                    </label>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Catatan:</strong> API Key dan Secret Key tidak bisa diubah. Gunakan tombol "Regenerate Secret Key" di halaman detail untuk membuat Secret Key baru.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
                <a href="{{ route('api-keys.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
