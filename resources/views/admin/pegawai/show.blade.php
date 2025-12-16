@extends('layouts.app')

@section('title', 'Detail Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user me-2"></i>Detail Pegawai</h2>
    <div>
        <a href="{{ route('pegawai.edit', $pegawai) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h4>{{ $pegawai->nama }}</h4>
                <p class="text-muted">{{ $pegawai->nip }}</p>
                @if($pegawai->role === 'admin')
                    <span class="badge bg-danger">Admin</span>
                @elseif($pegawai->role === 'pimpinan')
                    <span class="badge bg-warning">Pimpinan</span>
                @else
                    <span class="badge bg-primary">Pegawai</span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Pegawai</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">NIP</th>
                        <td><strong>{{ $pegawai->nip }}</strong></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td>{{ $pegawai->nama }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $pegawai->email }}</td>
                    </tr>
                    <tr>
                        <th>Rumpun Jabatan</th>
                        <td>{{ $pegawai->divisi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jabatan</th>
                        <td>{{ $pegawai->jabatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Satpelkes</th>
                        <td>{{ $pegawai->satpelkes->nama_satpelkes ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>
                            @if($pegawai->role === 'admin')
                                <span class="badge bg-danger">Admin</span>
                            @elseif($pegawai->role === 'pimpinan')
                                <span class="badge bg-warning">Pimpinan</span>
                            @else
                                <span class="badge bg-primary">Pegawai</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($pegawai->status === 'aktif')
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
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riwayat Presensi Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pegawai->presensi()->latest('waktu_absen')->limit(10)->get() as $presensi)
                                <tr>
                                    <td>{{ $presensi->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        @if($presensi->jenis === 'check_in')
                                            <span class="badge bg-success">Check In</span>
                                        @else
                                            <span class="badge bg-info">Check Out</span>
                                        @endif
                                    </td>
                                    <td>{{ $presensi->waktu_absen->format('H:i:s') }}</td>
                                    <td>
                                        @if($presensi->status === 'IN_ZONE')
                                            <span class="badge bg-success">IN ZONE</span>
                                        @elseif($presensi->status === 'OUT_ZONE_PENDING')
                                            <span class="badge bg-warning">PENDING</span>
                                        @elseif($presensi->status === 'APPROVED')
                                            <span class="badge bg-primary">APPROVED</span>
                                        @elseif($presensi->status === 'REJECTED')
                                            <span class="badge bg-danger">REJECTED</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('presensi.show', $presensi->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data presensi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    @if(auth('web')->user()->role === 'admin' || auth('web')->user()->role === 'pimpinan')
        <!-- Jadwal Pegawai -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Jadwal Kerja</h5>
                <a href="{{ route('jadwal-pegawai.create', ['pegawai_id' => $pegawai->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Tambah Jadwal
                </a>
            </div>
            <div class="card-body">
                @php
                    $jadwalList = $pegawai->jadwal()->aktif()->orderBy('hari')->get();
                @endphp
                @if($jadwalList->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Toleransi</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jadwalList as $jadwal)
                                    <tr>
                                        <td>{{ $jadwal->hari ?? 'Setiap Hari' }}</td>
                                        <td>{{ is_string($jadwal->jam_masuk) ? substr($jadwal->jam_masuk, 0, 5) : \Carbon\Carbon::parse($jadwal->jam_masuk)->format('H:i') }}</td>
                                        <td>{{ is_string($jadwal->jam_keluar) ? substr($jadwal->jam_keluar, 0, 5) : \Carbon\Carbon::parse($jadwal->jam_keluar)->format('H:i') }}</td>
                                        <td>{{ $jadwal->toleransi_telat }} menit</td>
                                        <td>
                                            <a href="{{ route('jadwal-pegawai.edit', $jadwal) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Belum ada jadwal kerja untuk pegawai ini.</p>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

