<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Absensi PPKP')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    @auth('web')
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <button class="btn btn-link text-white d-md-none me-2" type="button" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-calendar-check me-2"></i>
                <span class="d-none d-sm-inline">Sistem Absensi PPKP</span>
                <span class="d-sm-none">Absensi PPKP</span>
            </span>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3 d-none d-md-inline">
                    <i class="fas fa-user me-2"></i>
                    {{ Auth::guard('web')->user()->nama }}
                </span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-sign-out-alt d-none d-sm-inline me-1"></i>
                        <span class="d-sm-none"><i class="fas fa-sign-out-alt"></i></span>
                        <span class="d-none d-sm-inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0" id="sidebar">
                <nav class="nav flex-column">
                    @php
                        $userRole = auth('web')->user()->role;
                    @endphp
                    
                    <!-- Dashboard (All Roles) -->
                    <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    
                    <!-- Profile Pegawai (All Roles) -->
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                        <i class="fas fa-user"></i> Profile Pegawai
                    </a>
                    
                    <!-- Pegawai (Admin & Pimpinan Only) -->
                    @if($userRole === 'admin' || $userRole === 'pimpinan')
                        <a class="nav-link {{ request()->routeIs('pegawai.*') ? 'active' : '' }}" href="{{ route('pegawai.index') }}">
                            <i class="fas fa-users"></i> Pegawai
                        </a>
                    @endif
                    
                    <!-- Satpelkes (Admin & Pimpinan Only) -->
                    @if($userRole === 'admin' || $userRole === 'pimpinan')
                        <a class="nav-link {{ request()->routeIs('satpelkes.*') ? 'active' : '' }}" href="{{ route('satpelkes.index') }}">
                            <i class="fas fa-building"></i> Unit Kerja
                        </a>
                    @endif
                    
                    <!-- Jadwal Pegawai (Admin & Pimpinan Only) -->
                    @if($userRole === 'admin' || $userRole === 'pimpinan')
                        <a class="nav-link {{ request()->routeIs('jadwal-pegawai.*') ? 'active' : '' }}" href="{{ route('jadwal-pegawai.index') }}">
                            <i class="fas fa-calendar-alt"></i> Jadwal Pegawai
                        </a>
                    @endif
                    
                    <!-- Absensi (All Roles) -->
                    <a class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}" href="{{ route('absensi.index') }}">
                        <i class="fas fa-calendar-check"></i> Absensi
                    </a>
                    
                    <!-- Tugas Luar (All Roles) -->
                    <a class="nav-link {{ request()->routeIs('tugas-luar.*') ? 'active' : '' }}" href="{{ route('tugas-luar.index') }}">
                        <i class="fas fa-briefcase"></i> Tugas Luar
                    </a>
                    
                    <!-- Approval (Admin & Pimpinan Only) -->
                    @if($userRole === 'admin' || $userRole === 'pimpinan')
                        <a class="nav-link {{ request()->routeIs('approval.*') ? 'active' : '' }}" href="{{ route('approval.pending') }}">
                            <i class="fas fa-check-circle"></i> Approval
                        </a>
                    @endif
                    
                    <!-- Riwayat Presensi (All Roles) -->
                    <a class="nav-link {{ request()->routeIs('presensi.*') ? 'active' : '' }}" href="{{ route('presensi.index') }}">
                        <i class="fas fa-list"></i> Riwayat Presensi
                    </a>
                    
                    <!-- Laporan (Admin & Pimpinan Only) -->
                    @if($userRole === 'admin' || $userRole === 'pimpinan')
                        <a class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 col-12 main-content p-4">
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

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
    @else
        @yield('content')
    @endauth

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @auth('web')
    <script>
        // Mobile Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            }
            
            function closeSidebar() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }
            
            // Close sidebar when clicking on nav link (mobile)
            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        closeSidebar();
                    }
                });
            });
        });
    </script>
    @endauth
    
    @stack('scripts')
</body>
</html>

