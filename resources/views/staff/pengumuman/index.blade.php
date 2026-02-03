@extends('layouts.app')

@section('breadcrumb')
    <nav class="flex overflow-x-auto pb-1 invisible-scrollbar" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2 whitespace-nowrap">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                    <x-lucide-home class="w-4 h-4 sm:mr-2" />
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
                <x-lucide-chevron-right class="w-4 h-4 text-gray-400 mx-1 sm:mx-2" />
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
    <!-- Welcome Card -->
    <div class="mb-4 sm:mb-6">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl lg:rounded-3xl shadow-sm text-white overflow-hidden">
            <div class="p-5 sm:p-6 lg:p-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex-1 space-y-1">
                        <div class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-[10px] font-bold uppercase tracking-wider mb-1">
                            Pusat Informasi
                        </div>
                        <h4 class="text-xl sm:text-2xl font-black tracking-tight">
                            Pengumuman & Informasi
                        </h4>
                        <p class="text-blue-100 text-xs sm:text-sm">
                            Dapatkan informasi terbaru, tausiyah, dan pengumuman penting.
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-white/10 backdrop-blur-sm border border-white/20 p-3 sm:p-4 rounded-2xl text-center min-w-[100px]">
                            <h5 class="text-xl sm:text-2xl font-black mb-0.5">{{ count($announcements) }}</h5>
                            <p class="text-[10px] sm:text-xs text-blue-100 font-bold uppercase tracking-wider">Total</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mb-6">
        <div class="flex-1">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-500 transition-colors">
                    <x-lucide-search class="w-4 h-4" />
                </div>
                <input type="text" class="block w-full pl-10 pr-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm" 
                       placeholder="Cari pengumuman..." id="searchInput">
            </div>
        </div>
        <div class="flex gap-2 sm:gap-3">
            <div class="relative flex-1 sm:flex-none">
                <select class="block w-full pl-3 pr-8 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 appearance-none shadow-sm cursor-pointer" id="typeFilter">
                    <option value="">Semua Jenis</option>
                    <option value="tausiyah">Tausiyah</option>
                    <option value="kajian">Kajian</option>
                    <option value="pengumuman">Pengumuman</option>
                    <option value="himbauan">Himbauan</option>
                    <option value="undangan">Undangan</option>
                </select>
                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                    <x-lucide-chevron-down class="w-4 h-4" />
                </div>
            </div>
            <div class="relative flex-1 sm:flex-none">
                <select class="block w-full pl-3 pr-8 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 appearance-none shadow-sm cursor-pointer" id="priorityFilter">
                    <option value="">Prioritas</option>
                    <option value="high">Tinggi</option>
                    <option value="normal">Normal</option>
                    <option value="low">Rendah</option>
                </select>
                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                    <x-lucide-chevron-down class="w-4 h-4" />
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-3 transition-transform hover:scale-[1.02]">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <x-lucide-megaphone class="w-5 h-5" />
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Tausiyah</p>
                <p class="text-xl font-black text-gray-900 leading-none">{{ collect($announcements)->where('type', 'tausiyah')->count() }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-3 transition-transform hover:scale-[1.02]">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <x-lucide-book-open class="w-5 h-5" />
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Kajian</p>
                <p class="text-xl font-black text-gray-900 leading-none">{{ collect($announcements)->where('type', 'kajian')->count() }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-3 transition-transform hover:scale-[1.02]">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                <x-lucide-mail class="w-5 h-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5 truncate">Belum Baca</p>
                <p class="text-xl font-black text-gray-900 leading-none">{{ collect($announcements)->where('read_status', false)->count() }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-3 transition-transform hover:scale-[1.02]">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                <x-lucide-pin class="w-5 h-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Penting</p>
                <p class="text-xl font-black text-gray-900 leading-none">{{ collect($announcements)->where('is_pinned', true)->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 sm:px-8 py-5 sm:py-6 border-b border-gray-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="text-lg font-black text-gray-800 flex items-center">
                <x-lucide-list class="w-5 h-5 mr-3 text-blue-600" />
                Daftar Pengumuman
            </h3>
            <button onclick="markAllAsRead()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-blue-100 transition-colors">
                <x-lucide-check-check class="w-4 h-4 mr-2" />
                Tandai Semua Dibaca
            </button>
        </div>
        <div class="p-0">
            <div id="announcementsList" class="divide-y divide-gray-50">
                @forelse($announcements as $announcement)
                <div class="announcement-item p-5 sm:p-6 lg:p-8 {{ !$announcement['read_status'] ? 'bg-blue-50/30' : '' }} hover:bg-gray-50 transition-colors" 
                     data-type="{{ $announcement['type'] }}" 
                     data-priority="{{ $announcement['priority'] }}"
                     data-title="{{ strtolower($announcement['title']) }}">
                    <div class="flex flex-col lg:flex-row gap-4 lg:gap-8 lg:items-start">
                        <div class="flex-1 min-w-0 space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase tracking-wider border {{ getTypeBadgeClass($announcement['type']) }}">
                                    {{ $announcement['type'] }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase tracking-wider border {{ getPriorityBadgeClass($announcement['priority']) }}">
                                    {{ $announcement['priority'] }}
                                </span>
                                @if($announcement['is_pinned'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 text-[9px] font-bold uppercase tracking-wider">
                                        <x-lucide-pin class="w-3 h-3 mr-1" />
                                        Pinned
                                    </span>
                                @endif
                                @if(!$announcement['read_status'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-blue-100 text-blue-700 text-[9px] font-bold uppercase tracking-wider">
                                        Baru
                                    </span>
                                @endif
                            </div>

                            <h4 class="text-base sm:text-lg font-extrabold text-gray-900 tracking-tight leading-snug">
                                <a href="{{ route('staff.pengumuman.show', $announcement['id']) }}" class="hover:text-blue-600 transition-colors">
                                    {{ $announcement['title'] }}
                                </a>
                            </h4>

                            <p class="text-xs sm:text-sm text-gray-500 line-clamp-2 leading-relaxed">
                                {{ $announcement['content'] }}
                            </p>

                            <div class="flex flex-wrap items-center gap-4 text-[10px] sm:text-xs text-gray-400 font-bold uppercase tracking-wider pt-2">
                                <span class="flex items-center">
                                    <x-lucide-user class="w-3.5 h-3.5 mr-1.5 text-gray-300" />
                                    {{ $announcement['created_by'] }}
                                </span>
                                <span class="flex items-center">
                                    <x-lucide-calendar class="w-3.5 h-3.5 mr-1.5 text-gray-300" />
                                    {{ \Carbon\Carbon::parse($announcement['published_at'])->format('d M Y') }}
                                </span>
                                @if(count($announcement['attachments']) > 0)
                                    <span class="flex items-center text-blue-500">
                                        <x-lucide-paperclip class="w-3.5 h-3.5 mr-1.5" />
                                        {{ count($announcement['attachments']) }} File
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center lg:items-end lg:flex-col gap-2 flex-shrink-0">
                            <a href="{{ route('staff.pengumuman.show', $announcement['id']) }}" 
                               class="flex-1 lg:flex-none inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-bold uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                                <x-lucide-eye class="w-4 h-4 mr-2" />
                                Baca
                            </a>
                            @if(!$announcement['read_status'])
                                <button onclick="markAsRead({{ $announcement['id'] }}, this)" 
                                        class="inline-flex items-center justify-center px-3 py-2 bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-bold rounded-xl hover:bg-emerald-100 transition-all shadow-sm">
                                    <x-lucide-check class="w-4 h-4 lg:mr-2" />
                                    <span class="hidden lg:inline uppercase tracking-widest">Selesai</span>
                                </button>
                            @endif
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
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes slide-in-right {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in-right {
    animation: slide-in-right 0.3s ease-out;
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