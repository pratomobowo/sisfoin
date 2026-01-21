@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            @foreach($breadcrumbs as $breadcrumb)
                                @if($breadcrumb['url'])
                                    <li class="breadcrumb-item">
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                    </li>
                                @else
                                    <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['name'] }}</li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">Selamat Datang, {{ Auth::user()->name }}!</h4>
                            <p class="card-text text-muted mb-0">
                                Kelola aktivitas kerja Anda melalui menu-menu yang tersedia di bawah ini.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-muted">
                                <i class="fas fa-calendar-alt me-2"></i>
                                {{ now()->format('d F Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Menu Cards -->
    <div class="row">
        <!-- Absensi Card -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 menu-card">
                <div class="card-body text-center p-4">
                    <div class="menu-icon mb-3">
                        <i class="fas fa-clock fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title mb-3">Absensi</h5>
                    <p class="card-text text-muted mb-4">
                        Lihat data kehadiran, jam masuk, jam keluar, dan informasi lembur Anda.
                    </p>
                    <a href="{{ route('staff.absensi.index') }}" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-eye me-2"></i>
                        Lihat Absensi
                    </a>
                </div>
            </div>
        </div>

        <!-- Penggajian Card -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 menu-card">
                <div class="card-body text-center p-4">
                    <div class="menu-icon mb-3">
                        <i class="fas fa-money-bill-wave fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title mb-3">Penggajian</h5>
                    <p class="card-text text-muted mb-4">
                        Akses slip gaji bulanan Anda dan unduh dalam format PDF.
                    </p>
                    <a href="{{ route('staff.penggajian.index') }}" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        Lihat Slip Gaji
                    </a>
                </div>
            </div>
        </div>

        <!-- Pengumuman Card -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 menu-card">
                <div class="card-body text-center p-4">
                    <div class="menu-icon mb-3">
                        <i class="fas fa-bullhorn fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title mb-3">Pengumuman</h5>
                    <p class="card-text text-muted mb-4">
                        Baca pengumuman terbaru, informasi tausiyah, dan berita penting lainnya.
                    </p>
                    <a href="{{ route('staff.pengumuman.index') }}" class="btn btn-info btn-lg w-100">
                        <i class="fas fa-newspaper me-2"></i>
                        Lihat Pengumuman
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Ringkasan Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">22</h4>
                                <small class="text-muted">Hari Hadir Bulan Ini</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <h4 class="text-success mb-1">Rp 4.500.000</h4>
                                <small class="text-muted">Gaji Bulan Ini</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h4 class="text-info mb-1">3</h4>
                            <small class="text-muted">Pengumuman Baru</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.menu-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.menu-icon {
    transition: transform 0.2s ease-in-out;
}

.menu-card:hover .menu-icon {
    transform: scale(1.1);
}

.border-end {
    border-right: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .border-end {
        border-right: none;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    
    .border-end:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
}
</style>
@endsection