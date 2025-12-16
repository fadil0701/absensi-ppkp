@extends('layouts.app')

@section('title', 'Tambah Jadwal Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus me-2"></i>Tambah Jadwal Pegawai</h2>
    <a href="{{ route('jadwal-pegawai.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        @if(request('bulk'))
            <form action="{{ route('jadwal-pegawai.create-bulk') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Pegawai <span class="text-danger">*</span></label>
                    <select name="pegawai_id" class="form-select @error('pegawai_id') is-invalid @enderror" required>
                        <option value="">Pilih Pegawai</option>
                        @foreach($pegawaiList as $pegawai)
                            <option value="{{ $pegawai->id }}" {{ (old('pegawai_id') == $pegawai->id || ($selectedPegawai && $selectedPegawai->id == $pegawai->id)) ? 'selected' : '' }}>
                                {{ $pegawai->nama }} ({{ $pegawai->nip }})
                            </option>
                        @endforeach
                    </select>
                    @error('pegawai_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                            <input type="time" name="jam_masuk" id="jam_masuk_bulk" class="form-control @error('jam_masuk') is-invalid @enderror" 
                                   value="{{ old('jam_masuk', '07:30') }}" required>
                            @error('jam_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Jam Keluar <span class="text-danger">*</span></label>
                            <input type="time" name="jam_keluar" id="jam_keluar_bulk" class="form-control @error('jam_keluar') is-invalid @enderror" 
                                   value="{{ old('jam_keluar', '16:00') }}" required>
                            @error('jam_keluar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Toleransi Telat (menit)</label>
                            <input type="number" name="toleransi_telat" id="toleransi_bulk" class="form-control @error('toleransi_telat') is-invalid @enderror" 
                                   value="{{ old('toleransi_telat', '0') }}" min="0" max="60">
                            @error('toleransi_telat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Hari Kerja <span class="text-danger">*</span></label>
                    <div class="row">
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="hari[]" value="{{ $hari }}" id="hari_{{ $hari }}" 
                                           {{ in_array($hari, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']) && !old('hari') ? 'checked' : (in_array($hari, old('hari', [])) ? 'checked' : '') }}>
                                    <label class="form-check-label" for="hari_{{ $hari }}">
                                        {{ $hari }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('hari')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai (Opsional)</label>
                            <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                   value="{{ old('tanggal_mulai') }}">
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai (Opsional)</label>
                            <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                   value="{{ old('tanggal_selesai') }}">
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Simpan Jadwal Bulk
                    </button>
                </div>
            </form>
        @else
            <form action="{{ route('jadwal-pegawai.store-multiple') }}" method="POST" id="jadwalForm">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Pegawai <span class="text-danger">*</span></label>
                    <select name="pegawai_id" id="pegawai_id" class="form-select @error('pegawai_id') is-invalid @enderror" required>
                        <option value="">Pilih Pegawai</option>
                        @foreach($pegawaiList as $pegawai)
                            <option value="{{ $pegawai->id }}" {{ (old('pegawai_id') == $pegawai->id || ($selectedPegawai && $selectedPegawai->id == $pegawai->id)) ? 'selected' : '' }}>
                                {{ $pegawai->nama }} ({{ $pegawai->nip }})
                            </option>
                        @endforeach
                    </select>
                    @error('pegawai_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Jadwal Kerja</h5>
                        <button type="button" class="btn btn-success btn-sm" id="addRowBtn">
                            <i class="fas fa-plus me-1"></i>Tambah Row
                        </button>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-jadwal" id="jadwalTable">
                        <thead class="table-light">
                            <tr>
                                <th>Hari</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Toleransi Telat</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Aktif</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="jadwalTableBody">
                            <!-- Row pertama -->
                            <tr class="jadwal-row">
                                <td>
                                    <select name="jadwal[0][hari]" class="form-select form-select-sm hari-select">
                                        <option value="">Setiap Hari</option>
                                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                                            <option value="{{ $hari }}">{{ $hari }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="time" name="jadwal[0][jam_masuk]" class="form-control form-control-sm jam-masuk" value="07:30" required>
                                </td>
                                <td>
                                    <input type="time" name="jadwal[0][jam_keluar]" class="form-control form-control-sm jam-keluar" value="16:00" required>
                                </td>
                                <td>
                                    <input type="number" name="jadwal[0][toleransi_telat]" class="form-control form-control-sm toleransi-input" value="0" min="0" max="60">
                                </td>
                                <td>
                                    <input type="date" name="jadwal[0][tanggal_mulai]" class="form-control form-control-sm tanggal-mulai">
                                </td>
                                <td>
                                    <input type="date" name="jadwal[0][tanggal_selesai]" class="form-control form-control-sm tanggal-selesai">
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" name="jadwal[0][is_aktif]" value="1" class="form-check-input" checked>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan Jadwal
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
    let rowIndex = 1; // Mulai dari 1 karena row pertama sudah ada


    // Tambah row baru
    document.getElementById('addRowBtn')?.addEventListener('click', function() {
        const tbody = document.getElementById('jadwalTableBody');
        const newRow = document.createElement('tr');
        newRow.className = 'jadwal-row';
        
        newRow.innerHTML = `
            <td>
                <select name="jadwal[${rowIndex}][hari]" class="form-select form-select-sm hari-select">
                    <option value="">Setiap Hari</option>
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                        <option value="{{ $hari }}">{{ $hari }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="time" name="jadwal[${rowIndex}][jam_masuk]" class="form-control form-control-sm jam-masuk" value="07:30" required>
            </td>
            <td>
                <input type="time" name="jadwal[${rowIndex}][jam_keluar]" class="form-control form-control-sm jam-keluar" value="16:00" required>
            </td>
            <td>
                <input type="number" name="jadwal[${rowIndex}][toleransi_telat]" class="form-control form-control-sm toleransi" value="0" min="0" max="60" style="width: 70px">
            </td>
            <td>
                <input type="date" name="jadwal[${rowIndex}][tanggal_mulai]" class="form-control form-control-sm tanggal-mulai">
            </td>
            <td>
                <input type="date" name="jadwal[${rowIndex}][tanggal_selesai]" class="form-control form-control-sm tanggal-selesai">
            </td>
            <td class="text-center">
                <input type="checkbox" name="jadwal[${rowIndex}][is_aktif]" value="1" class="form-check-input" checked>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        rowIndex++;
        
        // Update remove button untuk semua row
        updateRemoveButtons();
    });

    // Hapus row
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const row = e.target.closest('.jadwal-row');
            const rowCount = document.querySelectorAll('.jadwal-row').length;
            
            if (rowCount > 1) {
                row.remove();
                updateRemoveButtons();
                // Re-index semua row
                reindexRows();
            } else {
                alert('Minimal harus ada satu row jadwal!');
            }
        }
    });

    // Update remove button (disable jika hanya 1 row)
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.jadwal-row');
        rows.forEach(row => {
            const removeBtn = row.querySelector('.remove-row');
            if (rows.length === 1) {
                removeBtn.disabled = true;
            } else {
                removeBtn.disabled = false;
            }
        });
    }

    // Re-index rows setelah delete
    function reindexRows() {
        const rows = document.querySelectorAll('.jadwal-row');
        rows.forEach((row, index) => {
            row.querySelectorAll('input, select').forEach(input => {
                const name = input.name;
                if (name) {
                    input.name = name.replace(/jadwal\[\d+\]/, `jadwal[${index}]`);
                }
            });
        });
        rowIndex = rows.length;
    }

    // Inisialisasi
    updateRemoveButtons();
</script>
@endsection

