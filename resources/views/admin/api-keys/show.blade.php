@extends('layouts.app')

@section('title', 'Detail API Key')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-key me-2"></i>Detail API Key</h2>
    <div>
        <a href="{{ route('api-keys.edit', $apiKey->id) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="{{ route('api-keys.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($createdKeys && $createdKeys['id'] == $apiKey->id)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Penting!</h5>
        <p class="mb-2"><strong>Simpan Secret Key dengan aman!</strong> Secret Key hanya ditampilkan sekali dan tidak bisa dilihat lagi setelah halaman ini ditutup.</p>
        <hr>
        <div class="mb-2">
            <strong>API Key:</strong>
            <div class="input-group mt-1">
                <input type="text" class="form-control" id="api_key" value="{{ $createdKeys['api_key'] }}" readonly>
                <button class="btn btn-outline-secondary copy-btn" type="button" data-copy-target="api_key">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
        <div class="mb-2">
            <strong>ConsID (Consumer ID):</strong>
            <div class="input-group mt-1">
                <input type="text" class="form-control" id="consid" value="{{ $createdKeys['consid'] }}" readonly>
                <button class="btn btn-outline-secondary copy-btn" type="button" data-copy-target="consid">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
        <div class="mb-2">
            <strong>UserKey:</strong>
            <div class="input-group mt-1">
                <input type="text" class="form-control" id="userkey" value="{{ $createdKeys['userkey'] }}" readonly>
                <button class="btn btn-outline-secondary copy-btn" type="button" data-copy-target="userkey">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
        <div class="mb-0">
            <strong>Secret Key:</strong>
            <div class="input-group mt-1">
                <input type="text" class="form-control" id="secret_key" value="{{ $createdKeys['secret_key'] }}" readonly>
                <button class="btn btn-outline-secondary copy-btn" type="button" data-copy-target="secret_key">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
    </div>
@endif

@if(session('secret_key_regenerated'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Secret Key Baru!</h5>
        <p class="mb-2"><strong>Simpan Secret Key baru dengan aman!</strong></p>
        <div class="mb-0">
            <strong>Secret Key Baru:</strong>
            <div class="input-group mt-1">
                <input type="text" class="form-control" id="new_secret_key" value="{{ session('secret_key_regenerated') }}" readonly>
                <button class="btn btn-outline-secondary copy-btn" type="button" data-copy-target="new_secret_key">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi API Key</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Nama</th>
                        <td>{{ $apiKey->name }}</td>
                    </tr>
                    <tr>
                        <th>API Key</th>
                        <td>
                            <code class="text-primary">{{ $apiKey->api_key }}</code>
                            <button class="btn btn-sm btn-link copy-text-btn" type="button" data-copy-text="{{ $apiKey->api_key }}">
                                <i class="fas fa-copy"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th>ConsID (Consumer ID)</th>
                        <td>
                            <code class="text-info">{{ $apiKey->consid ?? '-' }}</code>
                            @if($apiKey->consid)
                                <button class="btn btn-sm btn-link copy-text-btn" type="button" data-copy-text="{{ $apiKey->consid }}">
                                    <i class="fas fa-copy"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>UserKey</th>
                        <td>
                            <code class="text-success">{{ $apiKey->userkey ?? '-' }}</code>
                            @if($apiKey->userkey)
                                <button class="btn btn-sm btn-link copy-text-btn" type="button" data-copy-text="{{ $apiKey->userkey }}">
                                    <i class="fas fa-copy"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>{{ $apiKey->description ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Webhook URL</th>
                        <td>{{ $apiKey->webhook_url ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Allowed IPs</th>
                        <td>
                            @if($apiKey->allowed_ips && count($apiKey->allowed_ips) > 0)
                                {{ implode(', ', $apiKey->allowed_ips) }}
                            @else
                                <span class="text-muted">Semua IP diizinkan</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Scopes</th>
                        <td>
                            @if($apiKey->scopes && count($apiKey->scopes) > 0)
                                @foreach($apiKey->scopes as $scope)
                                    <span class="badge bg-info me-1">{{ $scope }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Semua akses</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Rate Limit</th>
                        <td>{{ $apiKey->rate_limit }} request/menit</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($apiKey->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Kadaluarsa</th>
                        <td>
                            @if($apiKey->expires_at)
                                {{ $apiKey->expires_at->format('d F Y') }}
                            @else
                                <span class="text-muted">Tidak ada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Terakhir Digunakan</th>
                        <td>
                            @if($apiKey->last_used_at)
                                {{ $apiKey->last_used_at->format('d F Y H:i:s') }}
                            @else
                                <span class="text-muted">Belum pernah</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>{{ $apiKey->created_at->format('d F Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Aksi</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('api-keys.regenerate-secret', $apiKey->id) }}" method="POST" class="mb-3" onsubmit="return confirm('Yakin ingin regenerate Secret Key? Secret Key lama tidak akan bisa digunakan lagi.');">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-sync me-1"></i>Regenerate Secret Key
                    </button>
                </form>
                
                <form action="{{ route('api-keys.destroy', $apiKey->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus API Key ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash me-1"></i>Hapus API Key
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Cara Penggunaan</h5>
            </div>
            <div class="card-body">
                <p class="small mb-2">Gunakan API Key di header request:</p>
                <code class="d-block small p-2 bg-light">
                    X-API-KEY: {{ $apiKey->api_key }}<br>
                    X-SECRET-KEY: [secret_key]
                </code>
                @if($apiKey->consid && $apiKey->userkey)
                    <p class="small mt-2 mb-2">Atau gunakan ConsID dan UserKey:</p>
                    <code class="d-block small p-2 bg-light">
                        X-CONSID: {{ $apiKey->consid }}<br>
                        X-USERKEY: {{ $apiKey->userkey }}<br>
                        X-SECRET-KEY: [secret_key]
                    </code>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy button untuk input field (menggunakan data-copy-target)
    document.querySelectorAll('.copy-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-copy-target');
            const input = document.getElementById(targetId);
            if (input) {
                const text = input.value;
                navigator.clipboard.writeText(text).then(function() {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    btn.classList.add('btn-success');
                    btn.classList.remove('btn-outline-secondary');
                    
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-secondary');
                    }, 2000);
                });
            }
        });
    });
    
    // Copy button untuk text langsung (menggunakan data-copy-text)
    document.querySelectorAll('.copy-text-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const text = this.getAttribute('data-copy-text');
            if (text) {
                navigator.clipboard.writeText(text).then(function() {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    btn.classList.add('text-success');
                    
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('text-success');
                    }, 2000);
                });
            }
        });
    });
});
</script>
@endpush
@endsection
