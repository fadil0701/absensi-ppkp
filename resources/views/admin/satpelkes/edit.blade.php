@extends('layouts.app')

@section('title', 'Edit Unit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Unit</h2>
    <a href="{{ route('satpelkes.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('satpelkes.update', $satpelke) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Kode Unit <span class="text-danger">*</span></label>
                        <input type="text" name="kode_satpelkes" class="form-control @error('kode_satpelkes') is-invalid @enderror" value="{{ old('kode_satpelkes', $satpelke->kode_satpelkes) }}" required>
                        @error('kode_satpelkes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                        <input type="text" name="nama_satpelkes" class="form-control @error('nama_satpelkes') is-invalid @enderror" value="{{ old('nama_satpelkes', $satpelke->nama_satpelkes) }}" required>
                        @error('nama_satpelkes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Latitude <span class="text-danger">*</span></label>
                        <input type="number" step="0.00000001" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $satpelke->latitude) }}" required>
                        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Longitude <span class="text-danger">*</span></label>
                        <input type="number" step="0.00000001" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $satpelke->longitude) }}" required>
                        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Radius Absensi (meter) <span class="text-danger">*</span></label>
                        <input type="number" name="radius_absensi" class="form-control @error('radius_absensi') is-invalid @enderror" value="{{ old('radius_absensi', $satpelke->radius_absensi) }}" min="10" required>
                        @error('radius_absensi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $satpelke->alamat) }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="is_aktif" class="form-select @error('is_aktif') is-invalid @enderror" required>
                            <option value="1" {{ old('is_aktif', $satpelke->is_aktif) == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_aktif', $satpelke->is_aktif) == 0 ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('is_aktif')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update
                </button>
                <a href="{{ route('satpelkes.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

