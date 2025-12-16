@extends('layouts.app')

@section('title', 'Detail Presensi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-clock me-2"></i>Detail Presensi</h2>
    <a href="{{ route('presensi.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Presensi</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Pegawai</th>
                        <td>
                            @if($presensi->pegawai)
                                <strong>{{ $presensi->pegawai->nama }}</strong><br>
                                <small class="text-muted">{{ $presensi->pegawai->nip }}</small>
                            @else
                                <span class="text-danger">Pegawai tidak ditemukan (ID: {{ $presensi->pegawai_id }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>{{ $presensi->tanggal->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>Jenis</th>
                        <td>
                            @if($presensi->jenis === 'check_in')
                                <span class="badge bg-success">Check In</span>
                            @else
                                <span class="badge bg-info">Check Out</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Waktu Absen</th>
                        <td>{{ $presensi->waktu_absen->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($presensi->status === 'IN_ZONE')
                                <span class="badge bg-success">IN ZONE</span>
                            @elseif($presensi->status === 'OUT_ZONE_PENDING')
                                <span class="badge bg-warning">OUT ZONE - PENDING</span>
                            @elseif($presensi->status === 'APPROVED')
                                <span class="badge bg-primary">APPROVED</span>
                            @elseif($presensi->status === 'REJECTED')
                                <span class="badge bg-danger">REJECTED</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Unit</th>
                        <td>{{ $presensi->satpelkes->nama_satpelkes ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jarak ke Satpelkes</th>
                        <td>{{ $presensi->jarak_ke_satpelkes ? number_format($presensi->jarak_ke_satpelkes, 2) . ' meter' : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Koordinat GPS</th>
                        <td>
                            <small>
                                Lat: {{ $presensi->latitude }}<br>
                                Lng: {{ $presensi->longitude }}<br>
                                Accuracy: {{ $presensi->accuracy ? number_format($presensi->accuracy, 2) . ' m' : '-' }}
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <th>Device ID</th>
                        <td><code>{{ $presensi->device_id }}</code></td>
                    </tr>
                    <tr>
                        <th>IP Address</th>
                        <td><code>{{ $presensi->ip_address }}</code></td>
                    </tr>
                    @if($presensi->keterangan)
                        <tr>
                            <th>Keterangan</th>
                            <td>{{ $presensi->keterangan }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        @if($presensi->foto_watermark)
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Foto dengan Watermark</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $presensi->foto_watermark) }}" 
                             alt="Foto Presensi" 
                             class="img-fluid rounded shadow-sm presensi-photo" 
                             class="img-presensi"
                             data-image-url="{{ asset('storage/' . $presensi->foto_watermark) }}">
                        <p class="text-muted mt-2 mb-0">
                            <small><i class="fas fa-info-circle me-1"></i>Klik foto untuk melihat ukuran penuh</small>
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        @if($presensi->foto_asli && $presensi->foto_asli !== $presensi->foto_watermark)
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-image me-2"></i>Foto Asli</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $presensi->foto_asli) }}" 
                             alt="Foto Asli" 
                             class="img-fluid rounded shadow-sm presensi-photo" 
                             class="img-presensi"
                             data-image-url="{{ asset('storage/' . $presensi->foto_asli) }}">
                        <p class="text-muted mt-2 mb-0">
                            <small><i class="fas fa-info-circle me-1"></i>Klik foto untuk melihat ukuran penuh</small>
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        @if($presensi->presensiLog->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Approval</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($presensi->presensiLog as $log)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $log->pimpinan ? $log->pimpinan->nama : 'Unknown' }}</strong><br>
                                        <small class="text-muted">{{ $log->waktu_action->format('d/m/Y H:i:s') }}</small>
                                    </div>
                                    <div>
                                        @if($log->action === 'approve')
                                            <span class="badge bg-success">APPROVED</span>
                                        @else
                                            <span class="badge bg-danger">REJECTED</span>
                                        @endif
                                    </div>
                                </div>
                                @if($log->catatan)
                                    <div class="mt-2">
                                        <small><strong>Catatan:</strong> {{ $log->catatan }}</small>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Foto Presensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Foto" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add click event to all presensi photos
        document.querySelectorAll('.presensi-photo').forEach(function(img) {
            img.addEventListener('click', function() {
                const imageUrl = this.getAttribute('data-image-url');
                document.getElementById('modalImage').src = imageUrl;
                const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                modal.show();
            });
        });
    });
</script>
@endpush
@endsection

