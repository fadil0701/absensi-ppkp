@extends('layouts.app')

@section('title', 'Laporan Tidak Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-times me-2"></i>Laporan Tidak Masuk</h2>
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
            <p class="text-muted">Total: {{ count($data) }} hari tidak masuk</p>
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
                            <th>Hari</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
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
                                <td>{{ $item['hari'] ?? '-' }}</td>
                                <td>{{ $item['jam_masuk'] ?? '-' }}</td>
                                <td>{{ $item['jam_keluar'] ?? '-' }}</td>
                                <td>{{ $item['keterangan'] ?? 'Tidak Masuk' }}</td>
                                <td>
                                    @if(str_contains(strtolower($item['status_keterangan'] ?? ''), 'tugas luar'))
                                        <span class="badge bg-info">Tugas Luar</span>
                                    @elseif(str_contains(strtolower($item['status_keterangan'] ?? ''), 'izin'))
                                        <span class="badge bg-primary">{{ $item['status_keterangan'] }}</span>
                                    @elseif(str_contains(strtolower($item['status_keterangan'] ?? ''), 'cuti'))
                                        <span class="badge bg-warning">{{ $item['status_keterangan'] }}</span>
                                    @elseif(str_contains(strtolower($item['status_keterangan'] ?? ''), 'sakit'))
                                        <span class="badge bg-danger">{{ $item['status_keterangan'] }}</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Masuk</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!isset($item['izin_cuti_id']) && $item['status_keterangan'] === 'Tidak Masuk')
                                        <a href="{{ route('izin-cuti.create', ['pegawai_id' => $item['pegawai_id'] ?? null, 'tanggal' => $item['tanggal']]) }}" 
                                           class="btn btn-sm btn-primary" title="Tambah Izin/Cuti/Sakit">
                                            <i class="fas fa-plus"></i> Izin/Cuti/Sakit
                                        </a>
                                    @elseif(isset($item['izin_cuti_id']))
                                        @if($item['izin_cuti_status'] === 'pending')
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="approveIzinCuti({{ $item['izin_cuti_id'] }})" title="Setujui">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="rejectIzinCuti({{ $item['izin_cuti_id'] }})" title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('izin-cuti.edit', $item['izin_cuti_id']) }}" 
                                           class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p>Semua pegawai hadir pada periode ini</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Approve -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Izin/Cuti/Sakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Masukkan keterangan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Izin/Cuti/Sakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_penolakan" class="form-control" rows="3" required placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveIzinCuti(id) {
    document.getElementById('approveForm').action = '{{ url("/izin-cuti") }}/' + id + '/approve';
    var modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectIzinCuti(id) {
    document.getElementById('rejectForm').action = '{{ url("/izin-cuti") }}/' + id + '/reject';
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endsection


