@extends('layouts.app')

@section('title', 'Edit Izin/Cuti')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Izin/Cuti</h2>
    <a href="{{ route('laporan.tidakMasuk', ['tanggal_mulai' => $izinCuti->tanggal->format('Y-m-d'), 'tanggal_selesai' => $izinCuti->tanggal->format('Y-m-d')]) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('izin-cuti.update', $izinCuti->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Pegawai</label>
                <input type="text" class="form-control" value="{{ $izinCuti->pegawai->nama }} ({{ $izinCuti->pegawai->nip }})" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="text" class="form-control" value="{{ $izinCuti->tanggal->format('d/m/Y') }} ({{ $izinCuti->tanggal->locale('id')->isoFormat('dddd') }})" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <input type="text" class="form-control" 
                       value="@if($izinCuti->status === 'disetujui') Disetujui @elseif($izinCuti->status === 'ditolak') Ditolak @else Pending @endif" 
                       disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Jenis <span class="text-danger">*</span></label>
                <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                    <option value="izin" {{ old('jenis', $izinCuti->jenis) == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="cuti" {{ old('jenis', $izinCuti->jenis) == 'cuti' ? 'selected' : '' }}>Cuti</option>
                    <option value="sakit" {{ old('jenis', $izinCuti->jenis) == 'sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
                @error('jenis')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                          rows="3" placeholder="Masukkan keterangan izin/cuti...">{{ old('keterangan', $izinCuti->keterangan) }}</textarea>
                @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($izinCuti->status === 'pending')
                <div class="mb-3">
                    <div class="d-flex gap-2">
                        <form action="{{ route('izin-cuti.approve', $izinCuti->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Setujui izin/cuti ini?')">
                                <i class="fas fa-check me-1"></i>Setujui
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                            <i class="fas fa-times me-1"></i>Tolak
                        </button>
                    </div>
                </div>
            @endif

            @if($izinCuti->status === 'ditolak' && $izinCuti->alasan_penolakan)
                <div class="alert alert-danger">
                    <strong>Alasan Penolakan:</strong> {{ $izinCuti->alasan_penolakan }}
                </div>
            @endif

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update
                </button>
            </div>
        </form>

        <!-- Modal Reject -->
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('izin-cuti.reject', $izinCuti->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tolak Izin/Cuti</h5>
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
    </div>
</div>

<script>
function showRejectModal() {
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endsection

