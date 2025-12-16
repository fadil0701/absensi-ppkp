@extends('layouts.app')

@section('title', 'Laporan Keterlambatan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-clock me-2"></i>Laporan Keterlambatan</h2>
    <div>
        <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="mb-4">
            <h5>Periode: {{ request('tanggal_mulai') }} s/d {{ request('tanggal_selesai') }}</h5>
            <p class="text-muted">Total: {{ count($data) }} pegawai terlambat</p>
        </div>

        @if(count($data) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Divisi</th>
                            <th>Jabatan</th>
                            <th>Unit</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Check-in</th>
                            <th>Menit Telat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $item['nip'] }}</strong></td>
                                <td>{{ $item['nama'] }}</td>
                                <td>{{ $item['divisi'] ?? '-' }}</td>
                                <td>{{ $item['jabatan'] ?? '-' }}</td>
                                <td>{{ $item['satpelkes_nama'] ?? '-' }}</td>
                                <td>{{ $item['tanggal'] }}</td>
                                <td>{{ $item['jam_masuk'] ?? '-' }}</td>
                                <td>{{ $item['jam_checkin'] ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-warning">{{ $item['menit_telat'] }} menit</span>
                                </td>
                                <td>{{ $item['status_kehadiran'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p>Tidak ada data keterlambatan pada periode ini</p>
            </div>
        @endif
    </div>
</div>
@endsection


