@extends('layouts.app')

@section('title', 'Jadwal Pegawai: ' . $pegawai->nama)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-alt me-2"></i>Jadwal: {{ $pegawai->nama }}</h2>
    <div>
        <a href="{{ route('jadwal-pegawai.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <a href="{{ route('jadwal-pegawai.create', ['pegawai_id' => $pegawai->id]) }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i>Tambah Jadwal
        </a>
    </div>
</div>

<!-- Informasi Pegawai -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>NIP:</strong> {{ $pegawai->nip }}
            </div>
            <div class="col-md-3">
                <strong>Divisi:</strong> {{ $pegawai->divisi }}
            </div>
            <div class="col-md-3">
                <strong>Jabatan:</strong> {{ $pegawai->jabatan }}
            </div>
            <div class="col-md-3">
                <strong>Satpelkes:</strong> 
                @if($pegawai->satpelkes)
                    {{ $pegawai->satpelkes->nama_satpelkes }}
                @else
                    <span class="text-muted">-</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Daftar Jadwal -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Jadwal Kerja</h5>
    </div>
    <div class="card-body">
        @if($pegawai->jadwal && $pegawai->jadwal->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Durasi</th>
                            <th>Toleransi Telat</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pegawai->jadwal as $jadwal)
                            <tr>
                                <td>
                                    @if($jadwal->hari)
                                        <span class="badge bg-info">{{ $jadwal->hari }}</span>
                                    @else
                                        <span class="text-muted">Setiap Hari</span>
                                    @endif
                                </td>
                                <td>{{ is_string($jadwal->jam_masuk) ? substr($jadwal->jam_masuk, 0, 5) : \Carbon\Carbon::parse($jadwal->jam_masuk)->format('H:i') }}</td>
                                <td>{{ is_string($jadwal->jam_keluar) ? substr($jadwal->jam_keluar, 0, 5) : \Carbon\Carbon::parse($jadwal->jam_keluar)->format('H:i') }}</td>
                                <td>
                                    @php
                                        // Parse jam_masuk
                                        if (is_string($jadwal->jam_masuk)) {
                                            // Cek format: H:i:s atau H:i
                                            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $jadwal->jam_masuk)) {
                                                $masuk = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_masuk);
                                            } elseif (preg_match('/^\d{2}:\d{2}$/', $jadwal->jam_masuk)) {
                                                $masuk = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_masuk);
                                            } else {
                                                $masuk = \Carbon\Carbon::parse($jadwal->jam_masuk);
                                            }
                                        } else {
                                            $masuk = \Carbon\Carbon::parse($jadwal->jam_masuk);
                                        }
                                        
                                        // Parse jam_keluar
                                        if (is_string($jadwal->jam_keluar)) {
                                            // Cek format: H:i:s atau H:i
                                            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $jadwal->jam_keluar)) {
                                                $keluar = \Carbon\Carbon::createFromFormat('H:i:s', $jadwal->jam_keluar);
                                            } elseif (preg_match('/^\d{2}:\d{2}$/', $jadwal->jam_keluar)) {
                                                $keluar = \Carbon\Carbon::createFromFormat('H:i', $jadwal->jam_keluar);
                                            } else {
                                                $keluar = \Carbon\Carbon::parse($jadwal->jam_keluar);
                                            }
                                        } else {
                                            $keluar = \Carbon\Carbon::parse($jadwal->jam_keluar);
                                        }
                                        
                                        if ($keluar->lt($masuk)) {
                                            $keluar->addDay();
                                        }
                                        $durasi = $masuk->diffInHours($keluar) . 'j ' . ($masuk->diffInMinutes($keluar) % 60) . 'm';
                                    @endphp
                                    {{ $durasi }}
                                </td>
                                <td>{{ $jadwal->toleransi_telat }} menit</td>
                                <td>
                                    @if($jadwal->tanggal_mulai || $jadwal->tanggal_selesai)
                                        <small>
                                            {{ $jadwal->tanggal_mulai ? $jadwal->tanggal_mulai->format('d/m/Y') : '?' }} - 
                                            {{ $jadwal->tanggal_selesai ? $jadwal->tanggal_selesai->format('d/m/Y') : '∞' }}
                                        </small>
                                    @else
                                        <span class="text-muted">Permanen</span>
                                    @endif
                                </td>
                                <td>
                                    @if($jadwal->is_aktif)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('jadwal-pegawai.edit', $jadwal) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('jadwal-pegawai.destroy', $jadwal) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Pegawai ini belum memiliki jadwal kerja. 
                <a href="{{ route('jadwal-pegawai.create', ['pegawai_id' => $pegawai->id]) }}" class="alert-link">Tambah jadwal sekarang</a>
            </div>
        @endif
    </div>
</div>
@endsection

