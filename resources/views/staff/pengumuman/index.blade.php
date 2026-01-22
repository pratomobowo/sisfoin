@extends('layouts.app')

@section('breadcrumb')
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                    Dashboard
                </a>
                <span class="text-gray-400 mx-2">&gt;</span>
            </li>
            <li>
                <span class="text-sm font-semibold text-gray-900">
                    Pengumuman
                </span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $title }}</h1>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            </div>
            <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg text-white">
            <div class="p-6">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                    <div class="flex-1">
                        <h4 class="text-xl font-semibold mb-2">
                            <i class="fas fa-bullhorn mr-2"></i>
                            Pengumuman & Informasi
                        </h4>
                        <p class="text-blue-100">
                            Dapatkan informasi terbaru, tausiyah, dan pengumuman penting dari perusahaan
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <div class="bg-white bg-opacity-20 p-4 rounded-lg text-center">
                            <h5 class="text-2xl font-bold mb-1">{{ count($announcements) }}</h5>
                            <small class="text-blue-100">Total Pengumuman</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Cari pengumuman..." id="searchInput">
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4">
                <div class="grid grid-cols-2 gap-2">
                    <select class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="typeFilter">
                        <option value="">Semua Jenis</option>
                        <option value="tausiyah">Tausiyah</option>
                        <option value="kajian">Kajian</option>
                        <option value="pengumuman">Pengumuman</option>
                        <option value="himbauan">Himbauan</option>
                        <option value="undangan">Undangan</option>
                    </select>
                    <select class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="priorityFilter">
                        <option value="">Semua Prioritas</option>
                        <option value="high">Tinggi</option>
                        <option value="normal">Normal</option>
                        <option value="low">Rendah</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-1">
                        <h6 class="text-green-600 text-sm font-medium mb-1">Tausiyah</h6>
                        <h4 class="text-2xl font-bold text-gray-900">{{ collect($announcements)->where('type', 'tausiyah')->count() }}</h4>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-mosque text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-1">
                        <h6 class="text-blue-600 text-sm font-medium mb-1">Kajian</h6>
                        <h4 class="text-2xl font-bold text-gray-900">{{ collect($announcements)->where('type', 'kajian')->count() }}</h4>
                    </div>
                    <div class="text-blue-500">
                        <i class="fas fa-book-open text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-1">
                        <h6 class="text-yellow-600 text-sm font-medium mb-1">Belum Dibaca</h6>
                        <h4 class="text-2xl font-bold text-gray-900">{{ collect($announcements)->where('read_status', false)->count() }}</h4>
                    </div>
                    <div class="text-yellow-500">
                        <i class="fas fa-envelope text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-purple-500">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-1">
                        <h6 class="text-purple-600 text-sm font-medium mb-1">Penting</h6>
                        <h4 class="text-2xl font-bold text-gray-900">{{ collect($announcements)->where('is_pinned', true)->count() }}</h4>
                    </div>
                    <div class="text-purple-500">
                        <i class="fas fa-thumbtack text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h6 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-list mr-2"></i>
                    Daftar Pengumuman
                </h6>
                <div class="flex gap-2">
                    <button class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="markAllAsRead()">
                        <i class="fas fa-check-double mr-1"></i>
                        Tandai Semua Dibaca
                    </button>
                </div>
            </div>
        </div>
        <div class="p-0">
            <div id="announcementsList">
                @forelse($announcements as $announcement)
                <div class="announcement-item border-b border-gray-200 p-6 {{ !$announcement['read_status'] ? 'bg-gray-50' : '' }}" 
                     data-type="{{ $announcement['type'] }}" 
                     data-priority="{{ $announcement['priority'] }}"
                     data-title="{{ strtolower($announcement['title']) }}">
                    <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                        <div class="flex-1">
                            <div class="flex items-start mb-2">
                                @if($announcement['is_pinned'])
                                    <i class="fas fa-thumbtack text-yellow-500 mr-2 mt-1"></i>
                                @endif
                                @if(!$announcement['read_status'])
                                    <div class="bg-blue-500 rounded-full mr-2 mt-2 w-2 h-2"></div>
                                @endif
                                <div class="flex-1">
                                    <h6 class="text-lg font-medium mb-1">
                                        <a href="{{ route('staff.pengumuman.show', $announcement['id']) }}" 
                                           class="text-gray-900 hover:text-blue-600 transition-colors duration-200">
                                            {{ $announcement['title'] }}
                                        </a>
                                    </h6>
                                    <p class="text-gray-600 mb-3 text-sm">
                                        {{ Str::limit($announcement['content'], 150) }}
                                    </p>
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getTypeBadgeClass($announcement['type']) }}">
                                            {{ ucfirst($announcement['type']) }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getPriorityBadgeClass($announcement['priority']) }}">
                                            {{ ucfirst($announcement['priority']) }}
                                        </span>
                                        @if(count($announcement['attachments']) > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-paperclip mr-1"></i>
                                                {{ count($announcement['attachments']) }} Lampiran
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:text-right space-y-2">
                            <div>
                                <small class="text-gray-500 text-sm">
                                    <i class="fas fa-user mr-1"></i>
                                    {{ $announcement['created_by'] }}
                                </small>
                            </div>
                            <div>
                                <small class="text-gray-500 text-sm">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ \Carbon\Carbon::parse($announcement['published_at'])->format('d M Y, H:i') }}
                                </small>
                            </div>
                            @if($announcement['expires_at'])
                            <div>
                                <small class="text-yellow-600 text-sm">
                                    <i class="fas fa-clock mr-1"></i>
                                    Berakhir: {{ \Carbon\Carbon::parse($announcement['expires_at'])->format('d M Y') }}
                                </small>
                            </div>
                            @endif
                            <div class="flex gap-2 lg:justify-end">
                                <a href="{{ route('staff.pengumuman.show', $announcement['id']) }}" 
                                   class="inline-flex items-center px-3 py-1.5 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-eye mr-1"></i>
                                    Baca
                                </a>
                                @if(!$announcement['read_status'])
                                <button class="inline-flex items-center px-3 py-1.5 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" 
                                        onclick="markAsRead({{ $announcement['id'] }})">
                                    <i class="fas fa-check mr-1"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                    <h5 class="text-xl font-medium text-gray-500 mb-2">Tidak ada pengumuman</h5>
                    <p class="text-gray-400">Belum ada pengumuman yang tersedia saat ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    filterAnnouncements();
});

// Filter functionality
document.getElementById('typeFilter').addEventListener('change', function() {
    filterAnnouncements();
});

document.getElementById('priorityFilter').addEventListener('change', function() {
    filterAnnouncements();
});

function filterAnnouncements() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const priorityFilter = document.getElementById('priorityFilter').value;
    
    const items = document.querySelectorAll('.announcement-item');
    
    items.forEach(item => {
        const title = item.dataset.title;
        const type = item.dataset.type;
        const priority = item.dataset.priority;
        
        const matchesSearch = title.includes(searchTerm);
        const matchesType = !typeFilter || type === typeFilter;
        const matchesPriority = !priorityFilter || priority === priorityFilter;
        
        if (matchesSearch && matchesType && matchesPriority) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function markAsRead(id) {
    // Simulate marking as read
    const button = event.target.closest('button');
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>';
    button.disabled = true;
    
    setTimeout(() => {
        // Remove unread indicator
        const item = button.closest('.announcement-item');
        item.classList.remove('bg-gray-50');
        
        // Remove blue dot
        const blueDot = item.querySelector('.bg-blue-500.rounded-full');
        if (blueDot) {
            blueDot.remove();
        }
        
        // Remove mark as read button
        button.remove();
        
        // Show success message
        showToast('Pengumuman ditandai sebagai sudah dibaca', 'success');
    }, 1000);
}

function markAllAsRead() {
    const unreadItems = document.querySelectorAll('.announcement-item.bg-gray-50');
    
    if (unreadItems.length === 0) {
        showToast('Semua pengumuman sudah dibaca', 'info');
        return;
    }
    
    unreadItems.forEach(item => {
        item.classList.remove('bg-gray-50');
        
        const blueDot = item.querySelector('.bg-blue-500.rounded-full');
        if (blueDot) {
            blueDot.remove();
        }
        
        const markButton = item.querySelector('button[onclick*="markAsRead"]');
        if (markButton) {
            markButton.remove();
        }
    });
    
    showToast(`${unreadItems.length} pengumuman ditandai sebagai sudah dibaca`, 'success');
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
</script>

<style>
.announcement-item {
    transition: all 0.3s ease;
}

.announcement-item:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-1px);
}

.badge {
    font-size: 0.75rem;
}

.btn-sm {
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .announcement-item .col-md-4 {
        margin-top: 1rem;
        text-align: left !important;
    }
    
    .announcement-item .d-flex.gap-1 {
        justify-content: start !important;
    }
}
</style>

@php
function getTypeBadgeClass($type) {
    return match($type) {
        'tausiyah' => 'bg-green-100 text-green-800',
        'kajian' => 'bg-blue-100 text-blue-800',
        'pengumuman' => 'bg-purple-100 text-purple-800',
        'himbauan' => 'bg-yellow-100 text-yellow-800',
        'undangan' => 'bg-indigo-100 text-indigo-800',
        default => 'bg-gray-100 text-gray-800'
    };
}

function getPriorityBadgeClass($priority) {
    return match($priority) {
        'low' => 'bg-gray-100 text-gray-800',
        'normal' => 'bg-blue-100 text-blue-800',
        'high' => 'bg-yellow-100 text-yellow-800',
        'urgent' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800'
    };
}
@endphp
@endsection