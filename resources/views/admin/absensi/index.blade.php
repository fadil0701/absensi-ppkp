@extends('layouts.app')

@section('title', 'Absensi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check me-2"></i>Absensi</h2>
    <span class="text-muted">{{ now()->format('d F Y, H:i') }}</span>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($tugasLuarPending)
    <div class="alert alert-warning mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Peringatan:</strong> Anda memiliki tugas luar yang masih menunggu persetujuan pimpinan.
        <br>
        <strong>Lokasi:</strong> {{ $tugasLuarPending->lokasi_tugas }} 
        ({{ $tugasLuarPending->tanggal_mulai->format('d/m/Y') }} - {{ $tugasLuarPending->tanggal_selesai->format('d/m/Y') }})
        <br>
        <small>Silakan tunggu persetujuan pimpinan sebelum melakukan absensi tugas luar.</small>
        <div class="mt-2">
            <a href="{{ route('tugas-luar.show', $tugasLuarPending) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-eye me-1"></i>Lihat Detail Tugas Luar
            </a>
        </div>
    </div>
@endif

@if($tugasLuar)
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Anda sedang tugas luar (DISETUJUI):</strong> {{ $tugasLuar->lokasi_tugas }} 
        ({{ $tugasLuar->tanggal_mulai->format('d/m/Y') }} - {{ $tugasLuar->tanggal_selesai->format('d/m/Y') }})
        <br>
        <small><strong>Absen tugas luar harus di-APPROVED pimpinan</strong> tanpa validasi zona GPS.</small>
    </div>
@endif

@if(!$tugasLuar && !$tugasLuarPending)
    <div class="alert alert-primary mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Informasi Absensi:</strong>
        <ul class="mb-0 mt-2">
            <li><strong>Absensi Rutin:</strong> Langsung APPROVED (tanpa approval)</li>
            <li><strong>Tugas Luar:</strong> Status PENDING (perlu approval pimpinan)</li>
        </ul>
    </div>
@endif

<div class="row">
    <!-- Status Hari Ini -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status Hari Ini</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Absen Masuk:</strong><br>
                    @if($checkInToday)
                        <span class="badge bg-success">{{ $checkInToday->waktu_absen->format('H:i:s') }}</span><br>
                        <small class="text-muted">
                            @if($checkInToday->status === 'IN_ZONE')
                                <span class="badge bg-success">IN ZONE</span>
                            @elseif($checkInToday->status === 'OUT_ZONE_PENDING')
                                <span class="badge bg-warning">PENDING</span>
                            @elseif($checkInToday->status === 'APPROVED')
                                <span class="badge bg-primary">APPROVED</span>
                            @endif
                        </small>
                    @else
                        <span class="text-muted">Belum Absen Masuk</span>
                    @endif
                </div>
                
                <div>
                    <strong>Absen Pulang:</strong><br>
                    @if($checkOutToday)
                        <span class="badge bg-info">{{ $checkOutToday->waktu_absen->format('H:i:s') }}</span>
                    @else
                        <span class="text-muted">Belum Absen Pulang</span>
                    @endif
                </div>
                
                @if($tugasLuar)
                    <div class="mt-3 pt-3 border-top">
                        <strong>Tugas Luar (Disetujui):</strong><br>
                        <span class="badge bg-info">{{ $tugasLuar->lokasi_tugas }}</span>
                        <br><small class="text-muted">Absen akan langsung APPROVED</small>
                    </div>
                @endif
                
                @if($tugasLuarPending)
                    <div class="mt-3 pt-3 border-top">
                        <strong>Tugas Luar (Pending):</strong><br>
                        <span class="badge bg-warning">{{ $tugasLuarPending->lokasi_tugas }}</span>
                        <br><small class="text-muted">Menunggu persetujuan pimpinan</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Form Absensi -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="absensiTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !$checkInToday ? 'active' : '' }}" 
                                id="checkin-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#checkin" 
                                type="button" 
                                role="tab"
                                {{ $checkInToday ? 'disabled' : '' }}>
                            <i class="fas fa-sign-in-alt me-1"></i>Absen Masuk
                            @if($checkInToday)
                                <span class="badge bg-success ms-1">✓</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $checkInToday && !$checkOutToday ? 'active' : '' }}" 
                                id="checkout-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#checkout" 
                                type="button" 
                                role="tab"
                                {{ !$checkInToday || $checkOutToday ? 'disabled' : '' }}>
                            <i class="fas fa-sign-out-alt me-1"></i>Absen Pulang
                            @if($checkOutToday)
                                <span class="badge bg-success ms-1">✓</span>
                            @endif
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="absensiTabsContent">
                    <!-- Check In Tab -->
                    <div class="tab-pane fade {{ !$checkInToday ? 'show active' : '' }}" 
                         id="checkin" 
                         role="tabpanel">
                        <form id="checkInForm" action="{{ route('absensi.checkin') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="latitude" id="checkin_latitude">
                            <input type="hidden" name="longitude" id="checkin_longitude">
                            <input type="hidden" name="accuracy" id="checkin_accuracy">
                            <input type="hidden" name="device_id" id="checkin_device_id">
                            <input type="hidden" name="foto" id="checkin_foto">
                            
                            <div class="mb-3">
                                <label class="form-label">Jenis Absensi <span class="text-danger">*</span></label>
                                <div class="border rounded p-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="jenis_absensi" id="checkin_absensi_rutin" value="rutin" checked>
                                        <label class="form-check-label" for="checkin_absensi_rutin">
                                            <strong>Absensi Rutin</strong>
                                            <br>
                                            <small class="text-muted">Absensi normal di lokasi satpelkes. Langsung APPROVED.</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jenis_absensi" id="checkin_tugas_luar" value="tugas_luar">
                                        <label class="form-check-label" for="checkin_tugas_luar">
                                            <strong>Tugas Luar</strong>
                                            <br>
                                            <small class="text-muted">Tidak perlu validasi zona GPS, Harus di APPROVED Pimpinan.</small>
                                        </label>
                                    </div>
                                </div>
                                <div id="checkin_tugas_luar_info" class="alert alert-info mt-2 d-hidden">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Informasi:</strong> Absensi tugas luar <strong>Harus APPROVED</strong> tanpa perlu validasi zona GPS.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Lokasi GPS</label>
                                <div id="checkin_location" class="alert alert-info">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <span id="checkin_location_text">Mengambil lokasi GPS...</span>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>Lokasi GPS tetap diperlukan untuk record, namun validasi zona hanya untuk absensi rutin
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Foto Selfie <span class="text-danger">*</span></label>
                                @if($tugasLuar)
                                    <div class="alert alert-warning mb-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Foto WAJIB diambil untuk absensi tugas luar!</strong>
                                    </div>
                                @endif
                                <div class="mb-2">
                                    <video id="checkin_video" width="100%" height="300" autoplay playsinline class="video-preview"></video>
                                    <canvas id="checkin_canvas" class="canvas-preview"></canvas>
                                    <img id="checkin_preview" src="" alt="Preview" class="image-preview">
                                </div>
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-primary" id="checkin_start_camera">
                                        <i class="fas fa-camera me-1"></i>Buka Kamera
                                    </button>
                                    <button type="button" class="btn btn-success d-hidden" id="checkin_capture" disabled>
                                        <i class="fas fa-camera-retro me-1"></i>Ambil Foto
                                    </button>
                                    <button type="button" class="btn btn-warning d-hidden" id="checkin_retake">
                                        <i class="fas fa-redo me-1"></i>Ulangi
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>Foto selfie wajib diambil untuk validasi kehadiran
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan <span id="checkin_keterangan_required" class="d-hidden text-required">*</span></label>
                                <textarea name="keterangan" id="checkin_keterangan" class="form-control" rows="2" placeholder="Masukkan keterangan..."></textarea>
                                <small id="checkin_keterangan_hint" class="text-muted d-block mt-1 d-hidden">
                                    <i class="fas fa-exclamation-circle me-1"></i>Keterangan wajib diisi untuk absensi tugas luar
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" id="checkin_submit" disabled>
                                <i class="fas fa-sign-in-alt me-1"></i>Absen Masuk
                            </button>
                        </form>
                    </div>

                    <!-- Check Out Tab -->
                    <div class="tab-pane fade {{ $checkInToday && !$checkOutToday ? 'show active' : '' }}" 
                         id="checkout" 
                         role="tabpanel">
                        <form id="checkOutForm" action="{{ route('absensi.checkout') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="latitude" id="checkout_latitude">
                            <input type="hidden" name="longitude" id="checkout_longitude">
                            <input type="hidden" name="accuracy" id="checkout_accuracy">
                            <input type="hidden" name="device_id" id="checkout_device_id">
                            <input type="hidden" name="foto" id="checkout_foto">
                            
                            <div class="mb-3">
                                <label class="form-label">Jenis Absensi <span class="text-danger">*</span></label>
                                <div class="border rounded p-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="jenis_absensi" id="checkout_absensi_rutin" value="rutin" checked>
                                        <label class="form-check-label" for="checkout_absensi_rutin">
                                            <strong>Absensi Rutin</strong>
                                            <br>
                                            <small class="text-muted">Absensi normal di lokasi satpelkes. Langsung APPROVED.</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jenis_absensi" id="checkout_tugas_luar" value="tugas_luar">
                                        <label class="form-check-label" for="checkout_tugas_luar">
                                            <strong>Tugas Luar</strong>
                                            <br>
                                            <small class="text-muted">Absensi di luar lokasi satpelkes. Tidak perlu validasi zona GPS, Harus di APPROVED Pimpinan.</small>
                                        </label>
                                    </div>
                                </div>
                                <div id="checkout_tugas_luar_info" class="alert alert-info mt-2 d-hidden">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Informasi:</strong> Absensi tugas luar <strong>Harus APPROVED</strong> tanpa perlu validasi zona GPS.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Lokasi GPS</label>
                                <div id="checkout_location" class="alert alert-info">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <span id="checkout_location_text">Mengambil lokasi GPS...</span>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>Lokasi GPS tetap diperlukan untuk record, namun validasi zona hanya untuk absensi rutin
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Foto Selfie <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    <video id="checkout_video" width="100%" height="300" autoplay playsinline class="video-preview"></video>
                                    <canvas id="checkout_canvas" class="canvas-preview"></canvas>
                                    <img id="checkout_preview" src="" alt="Preview" class="image-preview">
                                </div>
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-primary" id="checkout_start_camera">
                                        <i class="fas fa-camera me-1"></i>Buka Kamera
                                    </button>
                                    <button type="button" class="btn btn-success d-hidden" id="checkout_capture" disabled>
                                        <i class="fas fa-camera-retro me-1"></i>Ambil Foto
                                    </button>
                                    <button type="button" class="btn btn-warning d-hidden" id="checkout_retake">
                                        <i class="fas fa-redo me-1"></i>Ulangi
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan <span id="checkout_keterangan_required" class="d-hidden text-required">*</span></label>
                                <textarea name="keterangan" id="checkout_keterangan" class="form-control" rows="2" placeholder="Masukkan keterangan..."></textarea>
                                <small id="checkout_keterangan_hint" class="text-muted d-block mt-1 d-hidden">
                                    <i class="fas fa-exclamation-circle me-1"></i>Keterangan wajib diisi untuk absensi tugas luar
                                </small>
                            </div>

                            <button type="submit" class="btn btn-info w-100" id="checkout_submit" disabled>
                                <i class="fas fa-sign-out-alt me-1"></i>Absen Pulang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Generate Device ID
    let deviceId = localStorage.getItem('device_id');
    if (!deviceId) {
        deviceId = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('device_id', deviceId);
    }
    document.getElementById('checkin_device_id').value = deviceId;
    document.getElementById('checkout_device_id').value = deviceId;

    // Initialize camera buttons state
    function initializeCameraButtons() {
        // Check if there's already a photo in checkin
        const checkinFoto = document.getElementById('checkin_foto');
        const checkinPreview = document.getElementById('checkin_preview');
        if (checkinFoto && checkinFoto.value && checkinFoto.value.trim() !== '') {
            checkinPreview.src = checkinFoto.value;
            document.getElementById('checkin_start_camera').classList.add('d-hidden');
            checkinPreview.classList.add('active');
            document.getElementById('checkin_retake').classList.remove('d-hidden');
        } else {
            document.getElementById('checkin_start_camera').classList.remove('d-hidden');
            document.getElementById('checkin_capture').classList.add('d-hidden');
            document.getElementById('checkin_retake').classList.add('d-hidden');
        }

        // Check if there's already a photo in checkout
        const checkoutFoto = document.getElementById('checkout_foto');
        const checkoutPreview = document.getElementById('checkout_preview');
        if (checkoutFoto && checkoutFoto.value && checkoutFoto.value.trim() !== '') {
            checkoutPreview.src = checkoutFoto.value;
            document.getElementById('checkout_start_camera').classList.add('d-hidden');
            checkoutPreview.classList.add('active');
            document.getElementById('checkout_retake').classList.remove('d-hidden');
        } else {
            document.getElementById('checkout_start_camera').classList.remove('d-hidden');
            document.getElementById('checkout_capture').classList.add('d-hidden');
            document.getElementById('checkout_retake').classList.add('d-hidden');
        }
    }

    // Initialize on page load
    initializeCameraButtons();

    // Get GPS Location
    function getLocation(prefix) {
        const latInput = document.getElementById(prefix + '_latitude');
        const lngInput = document.getElementById(prefix + '_longitude');
        const accInput = document.getElementById(prefix + '_accuracy');
        const locationText = document.getElementById(prefix + '_location_text');
        const locationDiv = document.getElementById(prefix + '_location');

        if (!navigator.geolocation) {
            locationText.textContent = 'Geolocation tidak didukung browser Anda.';
            locationDiv.className = 'alert alert-danger';
            return;
        }

        locationText.textContent = 'Mengambil lokasi GPS...';
        locationDiv.className = 'alert alert-info';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const acc = position.coords.accuracy;

                latInput.value = lat;
                lngInput.value = lng;
                accInput.value = acc;

                locationText.innerHTML = `
                    <strong>Lokasi berhasil didapat:</strong><br>
                    Latitude: ${lat.toFixed(6)}<br>
                    Longitude: ${lng.toFixed(6)}<br>
                    Akurasi: ${acc.toFixed(2)} meter
                `;
                locationDiv.className = 'alert alert-success';
            },
            function(error) {
                let errorMsg = 'Error mengambil lokasi: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg += 'Izin lokasi ditolak.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg += 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMsg += 'Waktu request lokasi habis.';
                        break;
                    default:
                        errorMsg += 'Error tidak diketahui.';
                        break;
                }
                locationText.textContent = errorMsg;
                locationDiv.className = 'alert alert-danger';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    // Camera Functions for Check In
    let checkInStream = null;
    let checkInVideoReady = false;
    
    document.getElementById('checkin_start_camera').addEventListener('click', function() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
            .then(function(stream) {
                checkInStream = stream;
                const video = document.getElementById('checkin_video');
                video.srcObject = stream;
                video.classList.add('active');
                checkInVideoReady = false;
                document.getElementById('checkin_start_camera').classList.add('d-hidden');
                
                // Wait for video to be ready
                video.onloadedmetadata = function() {
                    video.play().then(function() {
                        checkInVideoReady = true;
                        const captureBtn = document.getElementById('checkin_capture');
                        captureBtn.classList.remove('d-hidden');
                        captureBtn.disabled = false;
                    }).catch(function(err) {
                        console.error('Error playing video:', err);
                        alert('Error memulai video: ' + err.message);
                    });
                };
            })
            .catch(function(err) {
                console.error('Error accessing camera:', err);
                alert('Error mengakses kamera: ' + err.message);
            });
    });

    document.getElementById('checkin_capture').addEventListener('click', function() {
        if (!checkInVideoReady) {
            alert('Video belum siap. Tunggu sebentar.');
            return;
        }

        const video = document.getElementById('checkin_video');
        const canvas = document.getElementById('checkin_canvas');
        const preview = document.getElementById('checkin_preview');
        const fotoInput = document.getElementById('checkin_foto');

        // Check if video is ready
        if (video.readyState === video.HAVE_ENOUGH_DATA && video.videoWidth > 0 && video.videoHeight > 0) {
            try {
                // Set canvas size to match video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                // Draw video frame to canvas
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convert to base64
                const base64 = canvas.toDataURL('image/jpeg', 0.8);
                
                // Validasi base64
                if (!base64 || base64 === 'data:,' || base64.length < 100) {
                    throw new Error('Gagal mengonversi foto ke base64. Pastikan kamera sudah aktif dan video sudah ditampilkan.');
                }
                
                // Pastikan base64 valid (minimal panjang dan format benar)
                if (!base64.startsWith('data:image/')) {
                    throw new Error('Format gambar tidak valid');
                }

                // Save to hidden input
                fotoInput.value = base64;
                preview.src = base64;
                preview.classList.add('active');
                video.classList.remove('active');
                document.getElementById('checkin_capture').classList.add('d-hidden');
                document.getElementById('checkin_retake').classList.remove('d-hidden');
                
                // Stop video stream
                if (checkInStream) {
                    checkInStream.getTracks().forEach(track => track.stop());
                    checkInStream = null;
                }
                checkInVideoReady = false;

                // Enable submit button
                enableSubmit('checkin');
                
                console.log('Foto berhasil diambil, ukuran:', (base64.length / 1024).toFixed(2), 'KB');
            } catch (error) {
                console.error('Error capturing photo:', error);
                alert('Error mengambil foto: ' + error.message);
            }
        } else {
            alert('Video belum siap. Pastikan kamera sudah aktif dan tampil.');
        }
    });

    document.getElementById('checkin_retake').addEventListener('click', function() {
        document.getElementById('checkin_preview').classList.remove('active');
        document.getElementById('checkin_foto').value = '';
        document.getElementById('checkin_retake').classList.add('d-hidden');
        document.getElementById('checkin_submit').disabled = true;
        checkInVideoReady = false;
        document.getElementById('checkin_start_camera').classList.remove('d-hidden');
        document.getElementById('checkin_start_camera').click();
    });

    // Camera Functions for Check Out
    let checkOutStream = null;
    let checkOutVideoReady = false;
    
    document.getElementById('checkout_start_camera').addEventListener('click', function() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
            .then(function(stream) {
                checkOutStream = stream;
                const video = document.getElementById('checkout_video');
                video.srcObject = stream;
                video.classList.add('active');
                checkOutVideoReady = false;
                document.getElementById('checkout_start_camera').classList.add('d-hidden');
                
                // Wait for video to be ready
                video.onloadedmetadata = function() {
                    video.play().then(function() {
                        checkOutVideoReady = true;
                        const captureBtn = document.getElementById('checkout_capture');
                        captureBtn.classList.remove('d-hidden');
                        captureBtn.disabled = false;
                    }).catch(function(err) {
                        console.error('Error playing video:', err);
                        alert('Error memulai video: ' + err.message);
                    });
                };
            })
            .catch(function(err) {
                console.error('Error accessing camera:', err);
                alert('Error mengakses kamera: ' + err.message);
            });
    });

    document.getElementById('checkout_capture').addEventListener('click', function() {
        if (!checkOutVideoReady) {
            alert('Video belum siap. Tunggu sebentar.');
            return;
        }

        const video = document.getElementById('checkout_video');
        const canvas = document.getElementById('checkout_canvas');
        const preview = document.getElementById('checkout_preview');
        const fotoInput = document.getElementById('checkout_foto');

        // Check if video is ready
        if (video.readyState === video.HAVE_ENOUGH_DATA && video.videoWidth > 0 && video.videoHeight > 0) {
            try {
                // Set canvas size to match video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                // Draw video frame to canvas
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convert to base64
                const base64 = canvas.toDataURL('image/jpeg', 0.8);
                
                // Validasi base64
                if (!base64 || base64 === 'data:,' || base64.length < 100) {
                    throw new Error('Gagal mengonversi foto ke base64. Pastikan kamera sudah aktif dan video sudah ditampilkan.');
                }
                
                // Pastikan base64 valid (minimal panjang dan format benar)
                if (!base64.startsWith('data:image/')) {
                    throw new Error('Format gambar tidak valid');
                }

                // Save to hidden input
                fotoInput.value = base64;
                preview.src = base64;
                preview.classList.add('active');
                video.classList.remove('active');
                document.getElementById('checkout_capture').classList.add('d-hidden');
                document.getElementById('checkout_retake').classList.remove('d-hidden');
                
                // Stop video stream
                if (checkOutStream) {
                    checkOutStream.getTracks().forEach(track => track.stop());
                    checkOutStream = null;
                }
                checkOutVideoReady = false;

                // Enable submit button
                enableSubmit('checkout');
                
                console.log('Foto berhasil diambil, ukuran:', (base64.length / 1024).toFixed(2), 'KB');
            } catch (error) {
                console.error('Error capturing photo:', error);
                alert('Error mengambil foto: ' + error.message);
            }
        } else {
            alert('Video belum siap. Pastikan kamera sudah aktif dan tampil.');
        }
    });

    document.getElementById('checkout_retake').addEventListener('click', function() {
        document.getElementById('checkout_preview').classList.remove('active');
        document.getElementById('checkout_foto').value = '';
        document.getElementById('checkout_retake').classList.add('d-hidden');
        document.getElementById('checkout_submit').disabled = true;
        checkOutVideoReady = false;
        document.getElementById('checkout_start_camera').classList.remove('d-hidden');
        document.getElementById('checkout_start_camera').click();
    });

    // Handle jenis absensi radio button change
    function handleJenisAbsensiChange(prefix) {
        const tugasLuarRadio = document.getElementById(prefix + '_tugas_luar');
        const rutinRadio = document.getElementById(prefix + '_absensi_rutin');
        const tugasLuarInfo = document.getElementById(prefix + '_tugas_luar_info');
        const keteranganRequired = document.getElementById(prefix + '_keterangan_required');
        const keteranganHint = document.getElementById(prefix + '_keterangan_hint');
        const keteranganField = document.getElementById(prefix + '_keterangan');
        
        if (tugasLuarRadio && rutinRadio) {
            tugasLuarRadio.addEventListener('change', function() {
                if (this.checked) {
                    tugasLuarInfo.style.display = 'block';
                    keteranganRequired.style.display = 'inline';
                    keteranganHint.style.display = 'block';
                    keteranganField.required = true;
                    keteranganField.placeholder = 'Masukkan keterangan tugas luar (wajib)';
                }
                checkSubmitButton(prefix);
            });
            
            rutinRadio.addEventListener('change', function() {
                if (this.checked) {
                    tugasLuarInfo.classList.add('d-hidden');
                    keteranganRequired.classList.add('d-hidden');
                    keteranganHint.classList.add('d-hidden');
                    keteranganField.required = false;
                    keteranganField.placeholder = 'Masukkan keterangan jika ada...';
                }
                checkSubmitButton(prefix);
            });
        }
    }

    function checkSubmitButton(prefix) {
        const lat = document.getElementById(prefix + '_latitude').value;
        const foto = document.getElementById(prefix + '_foto').value;
        const submitBtn = document.getElementById(prefix + '_submit');
        const tugasLuarRadio = document.getElementById(prefix + '_tugas_luar');
        const keterangan = document.getElementById(prefix + '_keterangan').value;
        
        let isValid = lat && foto;
        
        // Jika tugas luar dipilih, keterangan wajib
        if (tugasLuarRadio && tugasLuarRadio.checked) {
            isValid = isValid && keterangan && keterangan.trim() !== '';
        }
        
        submitBtn.disabled = !isValid;
    }

    function enableSubmit(prefix) {
        checkSubmitButton(prefix);
    }
    
    // Initialize jenis absensi handlers
    handleJenisAbsensiChange('checkin');
    handleJenisAbsensiChange('checkout');

    // Get location when tab is shown
    document.getElementById('checkin-tab').addEventListener('shown.bs.tab', function() {
        getLocation('checkin');
    });

    document.getElementById('checkout-tab').addEventListener('shown.bs.tab', function() {
        getLocation('checkout');
    });

    // Get location on page load for active tab
    window.addEventListener('load', function() {
        const activeTab = document.querySelector('#absensiTabs button.active');
        if (activeTab) {
            if (activeTab.id === 'checkin-tab') {
                getLocation('checkin');
            } else if (activeTab.id === 'checkout-tab') {
                getLocation('checkout');
            }
        }
    });

    // Form validation before submit
    document.getElementById('checkInForm').addEventListener('submit', function(e) {
        const foto = document.getElementById('checkin_foto').value;
        const lat = document.getElementById('checkin_latitude').value;
        const tugasLuarRadio = document.getElementById('checkin_tugas_luar');
        const keterangan = document.getElementById('checkin_keterangan').value;
        
        if (!foto || foto.trim() === '') {
            e.preventDefault();
            alert('⚠️ Foto wajib diambil sebelum melakukan absensi! Silakan ambil foto terlebih dahulu.');
            return false;
        }
        
        if (!lat || lat.trim() === '') {
            e.preventDefault();
            alert('⚠️ Lokasi GPS wajib didapatkan! Pastikan browser mengizinkan akses lokasi.');
            return false;
        }
        
        // Jika tugas luar dipilih, keterangan wajib
        if (tugasLuarRadio && tugasLuarRadio.checked && (!keterangan || keterangan.trim() === '')) {
            e.preventDefault();
            alert('⚠️ Keterangan wajib diisi untuk absensi tugas luar!');
            return false;
        }
    });

    document.getElementById('checkOutForm').addEventListener('submit', function(e) {
        const foto = document.getElementById('checkout_foto').value;
        const lat = document.getElementById('checkout_latitude').value;
        const tugasLuarRadio = document.getElementById('checkout_tugas_luar');
        const keterangan = document.getElementById('checkout_keterangan').value;
        
        if (!foto || foto.trim() === '') {
            e.preventDefault();
            alert('⚠️ Foto wajib diambil sebelum melakukan absensi! Silakan ambil foto terlebih dahulu.');
            return false;
        }
        
        if (!lat || lat.trim() === '') {
            e.preventDefault();
            alert('⚠️ Lokasi GPS wajib didapatkan! Pastikan browser mengizinkan akses lokasi.');
            return false;
        }
        
        // Jika tugas luar dipilih, keterangan wajib
        if (tugasLuarRadio && tugasLuarRadio.checked && (!keterangan || keterangan.trim() === '')) {
            e.preventDefault();
            alert('⚠️ Keterangan wajib diisi untuk absensi tugas luar!');
            return false;
        }
    });
    
    // Update submit button when keterangan changes
    document.getElementById('checkin_keterangan').addEventListener('input', function() {
        checkSubmitButton('checkin');
    });
    document.getElementById('checkout_keterangan').addEventListener('input', function() {
        checkSubmitButton('checkout');
    });

    // Cleanup streams on page unload
    window.addEventListener('beforeunload', function() {
        if (checkInStream) {
            checkInStream.getTracks().forEach(track => track.stop());
        }
        if (checkOutStream) {
            checkOutStream.getTracks().forEach(track => track.stop());
        }
    });
</script>
@endpush
@endsection

