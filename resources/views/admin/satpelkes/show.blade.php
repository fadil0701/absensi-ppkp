@extends('layouts.app')

@section('title', 'Detail Unit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-building me-2"></i>Detail Unit</h2>
    <div>
        <a href="{{ route('satpelkes.edit', $satpelke) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="{{ route('satpelkes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Unit</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Kode Unit</th>
                        <td><strong>{{ $satpelke->kode_satpelkes }}</strong></td>
                    </tr>
                    <tr>
                        <th>Nama Unit</th>
                        <td>{{ $satpelke->nama_satpelkes }}</td>
                    </tr>
                    <tr>
                        <th>Latitude</th>
                        <td>{{ $satpelke->latitude }}</td>
                    </tr>
                    <tr>
                        <th>Longitude</th>
                        <td>{{ $satpelke->longitude }}</td>
                    </tr>
                    <tr>
                        <th>Radius Absensi</th>
                        <td>{{ $satpelke->radius_absensi }} meter</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $satpelke->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($satpelke->is_aktif)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Pegawai di Unit ini</h5>
            </div>
            <div class="card-body">
                @if($satpelke->pegawai->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($satpelke->pegawai as $pegawai)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $pegawai->nama }}</strong><br>
                                    <small class="text-muted">{{ $pegawai->nip }}</small>
                                </div>
                                <a href="{{ route('pegawai.show', $pegawai) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">Belum ada pegawai di unit ini</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

