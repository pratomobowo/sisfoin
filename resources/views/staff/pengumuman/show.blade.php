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
                <div class="d-flex gap-2">
                    @if(!$announcement['read_status'])
                    <button onclick="markAsRead()" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>
                        Tandai Dibaca
                    </button>
                    @endif
                    <button onclick="window.print()" class="btn btn-outline-primary">
                        <i class="fas fa-print me-2"></i>
                        Cetak
                    </button>
                    <a href="{{ route('staff.pengumuman.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcement Detail -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Main Content -->
            <div class="card border-0 shadow-sm mb-4">
                <!-- Header -->
                <div class="card-header bg-white border-bottom py-4">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                @if($announcement['is_pinned'])
                                    <i class="fas fa-thumbtack text-warning me-2"></i>
                                @endif
                                @if(!$announcement['read_status'])
                                    <div class="bg-primary rounded-circle me-2" style="width: 10px; height: 10px;"></div>
                                @endif
                                <h4 class="mb-0 text-dark">{{ $announcement['title'] }}</h4>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge {{ getTypeBadge($announcement['type']) }} fs-6">
                                    <i class="fas fa-tag me-1"></i>
                                    {{ ucfirst($announcement['type']) }}
                                </span>
                                <span class="badge {{ getPriorityBadge($announcement['priority']) }} fs-6">
                                    <i class="fas fa-flag me-1"></i>
                                    Prioritas {{ ucfirst($announcement['priority']) }}
                                </span>
                                @if($announcement['is_pinned'])
                                <span class="badge bg-warning fs-6">
                                    <i class="fas fa-thumbtack me-1"></i>
                                    Disematkan
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="card-body p-4">
                    <div class="announcement-content">
                        {!! nl2br(e($announcement['content'])) !!}
                    </div>

                    @if(count($announcement['attachments']) > 0)
                    <!-- Attachments -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-paperclip me-2"></i>
                            Lampiran ({{ count($announcement['attachments']) }})
                        </h6>
                        <div class="row g-3">
                            @foreach($announcement['attachments'] as $attachment)
                            <div class="col-md-6">
                                <div class="card border border-light">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                @if(Str::endsWith($attachment, '.pdf'))
                                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                                @elseif(Str::endsWith($attachment, ['.jpg', '.jpeg', '.png']))
                                                    <i class="fas fa-file-image fa-2x text-success"></i>
                                                @elseif(Str::endsWith($attachment, ['.doc', '.docx']))
                                                    <i class="fas fa-file-word fa-2x text-primary"></i>
                                                @else
                                                    <i class="fas fa-file fa-2x text-muted"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $attachment }}</h6>
                                                <small class="text-muted">
                                                    @if(Str::endsWith($attachment, '.pdf'))
                                                        Dokumen PDF
                                                    @elseif(Str::endsWith($attachment, ['.jpg', '.jpeg', '.png']))
                                                        Gambar
                                                    @elseif(Str::endsWith($attachment, ['.doc', '.docx']))
                                                        Dokumen Word
                                                    @else
                                                        File
                                                    @endif
                                                </small>
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="downloadAttachment('{{ $attachment }}')">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="card-footer bg-light border-top-0 py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                Dibuat oleh: <strong>{{ $announcement['created_by'] }}</strong>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($announcement['published_at'])->format('d F Y, H:i') }} WIB
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Pengumuman
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Jenis</span>
                            <span class="badge {{ getTypeBadge($announcement['type']) }}">
                                {{ ucfirst($announcement['type']) }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Prioritas</span>
                            <span class="badge {{ getPriorityBadge($announcement['priority']) }}">
                                {{ ucfirst($announcement['priority']) }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-success">
                                {{ ucfirst($announcement['status']) }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Tanggal Publikasi</span>
                            <span>{{ \Carbon\Carbon::parse($announcement['published_at'])->format('d M Y') }}</span>
                        </div>
                        @if($announcement['expires_at'])
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Berakhir</span>
                            <span class="text-warning">
                                {{ \Carbon\Carbon::parse($announcement['expires_at'])->format('d M Y') }}
                            </span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Lampiran</span>
                            <span>{{ count($announcement['attachments']) }} file</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Status Baca</span>
                            @if($announcement['read_status'])
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>
                                    Sudah Dibaca
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-envelope me-1"></i>
                                    Belum Dibaca
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Aksi
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        @if(!$announcement['read_status'])
                        <button onclick="markAsRead()" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>
                            Tandai Sebagai Dibaca
                        </button>
                        @endif
                        <button onclick="window.print()" class="btn btn-outline-primary">
                            <i class="fas fa-print me-2"></i>
                            Cetak Pengumuman
                        </button>
                        <button onclick="shareAnnouncement()" class="btn btn-outline-info">
                            <i class="fas fa-share me-2"></i>
                            Bagikan
                        </button>
                        <a href="{{ route('staff.pengumuman.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>
                            Lihat Semua Pengumuman
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsRead() {
    const button = event.target;
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
    button.disabled = true;
    
    setTimeout(() => {
        // Remove unread indicators
        const unreadDots = document.querySelectorAll('.bg-primary.rounded-circle');
        unreadDots.forEach(dot => dot.remove());
        
        // Update status badge
        const statusBadge = document.querySelector('.list-group-item:last-child .badge');
        if (statusBadge) {
            statusBadge.className = 'badge bg-success';
            statusBadge.innerHTML = '<i class="fas fa-check me-1"></i>Sudah Dibaca';
        }
        
        // Remove mark as read buttons
        const markButtons = document.querySelectorAll('button[onclick="markAsRead()"]');
        markButtons.forEach(btn => btn.remove());
        
        showToast('Pengumuman berhasil ditandai sebagai sudah dibaca', 'success');
    }, 1000);
}

function downloadAttachment(filename) {
    const button = event.target;
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
        
        showToast(`File ${filename} akan segera diunduh`, 'info');
    }, 1000);
}

function shareAnnouncement() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $announcement["title"] }}',
            text: '{{ Str::limit($announcement["content"], 100) }}',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            showToast('Link pengumuman berhasil disalin ke clipboard', 'success');
        });
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 3000);
}
</script>

<style>
.announcement-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.announcement-content p {
    margin-bottom: 1.5rem;
}

@media print {
    .btn, .breadcrumb, .card-footer {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    body {
        font-size: 12px;
    }
    
    .announcement-content {
        font-size: 14px;
    }
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-1px);
}

.badge {
    font-size: 0.8rem;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>

@php
function getTypeBadge($type) {
    return match($type) {
        'tausiyah' => 'bg-success',
        'kajian' => 'bg-info',
        'pengumuman' => 'bg-primary',
        'himbauan' => 'bg-warning',
        'undangan' => 'bg-purple',
        default => 'bg-secondary'
    };
}

function getPriorityBadge($priority) {
    return match($priority) {
        'low' => 'bg-secondary',
        'normal' => 'bg-primary',
        'high' => 'bg-warning',
        'urgent' => 'bg-danger',
        default => 'bg-secondary'
    };
}
@endphp
@endsection