@extends('layouts.app')

@section('title', 'API Key Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-key me-2"></i>API Key Management</h2>
    <a href="{{ route('api-keys.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah API Key
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>API Key</th>
                        <th>Webhook URL</th>
                        <th>Rate Limit</th>
                        <th>Status</th>
                        <th>Terakhir Digunakan</th>
                        <th>Kadaluarsa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($apiKeys as $key)
                        <tr>
                            <td>
                                <strong>{{ $key->name }}</strong>
                                @if($key->description)
                                    <br><small class="text-muted">{{ Str::limit($key->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <code class="text-primary">{{ Str::limit($key->api_key, 20) }}...</code>
                                <button class="btn btn-sm btn-link" onclick="copyToClipboard('{{ $key->api_key }}')" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </td>
                            <td>
                                @if($key->webhook_url)
                                    <small>{{ Str::limit($key->webhook_url, 30) }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $key->rate_limit }}/menit</td>
                            <td>
                                @if($key->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if($key->last_used_at)
                                    {{ $key->last_used_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">Belum pernah</span>
                                @endif
                            </td>
                            <td>
                                @if($key->expires_at)
                                    {{ $key->expires_at->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Tidak ada</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('api-keys.show', $key->id) }}" class="btn btn-sm btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('api-keys.edit', $key->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('api-keys.destroy', $key->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus API Key ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted mb-0">Belum ada API Key</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('API Key berhasil disalin!');
    });
}
</script>
@endpush
@endsection
