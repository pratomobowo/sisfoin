@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-4 sm:space-y-6">
    {{-- Breadcrumb --}}
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                    Dashboard
                </a>
                <span class="text-gray-400 mx-2">&gt;</span>
            </li>
            <li class="inline-flex items-center">
                <a href="{{ route('staff.pengumuman.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                    Pengumuman
                </a>
                <span class="text-gray-400 mx-2">&gt;</span>
            </li>
            <li>
                <span class="text-sm font-semibold text-gray-900">
                    Detail
                </span>
            </li>
        </ol>
    </nav>

    {{-- Header Section --}}
    <div class="bg-white rounded-2xl lg:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex-1 min-w-0">
                    {{-- Badges --}}
                    <div class="flex flex-wrap items-center gap-2 mb-3 sm:mb-4">
                        @if($announcement['is_pinned'])
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-[10px] sm:text-xs font-bold uppercase tracking-wider border border-amber-100">
                                <x-lucide-pin class="w-3 h-3 sm:w-3.5 sm:h-3.5 mr-1.5" />
                                Disematkan
                            </span>
                        @endif
                        @if(!$announcement['read_status'])
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-[10px] sm:text-xs font-bold uppercase tracking-wider border border-blue-100">
                                <div class="w-2 h-2 rounded-full bg-blue-600 mr-1.5 animate-pulse"></div>
                                Belum Dibaca
                            </span>
                        @endif
                        @php
                            $typeBadges = [
                                'tausiyah' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'kajian' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
                                'pengumuman' => 'bg-blue-50 text-blue-700 border-blue-100',
                                'himbauan' => 'bg-amber-50 text-amber-700 border-amber-100',
                                'undangan' => 'bg-purple-50 text-purple-700 border-purple-100',
                            ];
                            $typeClass = $typeBadges[$announcement['type']] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                            
                            $priorityBadges = [
                                'low' => 'bg-gray-50 text-gray-700 border-gray-100',
                                'normal' => 'bg-blue-50 text-blue-700 border-blue-100',
                                'high' => 'bg-amber-50 text-amber-700 border-amber-100',
                                'urgent' => 'bg-rose-50 text-rose-700 border-rose-100',
                            ];
                            $priorityClass = $priorityBadges[$announcement['priority']] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] sm:text-xs font-bold uppercase tracking-wider border {{ $typeClass }}">
                            {{ ucfirst($announcement['type']) }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] sm:text-xs font-bold uppercase tracking-wider border {{ $priorityClass }}">
                            Prioritas {{ ucfirst($announcement['priority']) }}
                        </span>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-gray-900 tracking-tight mb-2">
                        {{ $announcement['title'] }}
                    </h1>

                    {{-- Meta Info --}}
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs sm:text-sm text-gray-500">
                        <div class="flex items-center">
                            <x-lucide-user class="w-4 h-4 mr-1.5 flex-shrink-0" />
                            <span class="font-medium">{{ $announcement['created_by'] }}</span>
                        </div>
                        <div class="flex items-center">
                            <x-lucide-calendar class="w-4 h-4 mr-1.5 flex-shrink-0" />
                            <span>{{ \Carbon\Carbon::parse($announcement['published_at'])->format('d F Y, H:i') }} WIB</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$announcement['read_status'])
                        <button onclick="markAsRead()" class="inline-flex items-center px-3 sm:px-4 py-2 sm:py-2.5 bg-emerald-600 text-white text-xs sm:text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                            <x-lucide-check class="w-4 h-4 mr-1.5" />
                            <span class="hidden sm:inline">Tandai Dibaca</span>
                            <span class="sm:hidden">Dibaca</span>
                        </button>
                    @endif
                    <button onclick="window.print()" class="inline-flex items-center px-3 sm:px-4 py-2 sm:py-2.5 bg-white border border-gray-200 text-gray-700 text-xs sm:text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                        <x-lucide-printer class="w-4 h-4 sm:mr-1.5" />
                        <span class="hidden sm:inline">Cetak</span>
                    </button>
                    <a href="{{ route('staff.pengumuman.index') }}" class="inline-flex items-center px-3 sm:px-4 py-2 sm:py-2.5 bg-white border border-gray-200 text-gray-700 text-xs sm:text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                        <x-lucide-arrow-left class="w-4 h-4 sm:mr-1.5" />
                        <span class="hidden sm:inline">Kembali</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            {{-- Content Card --}}
            <div class="bg-white rounded-2xl lg:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8 lg:p-10">
                    <div class="prose prose-sm sm:prose lg:prose-lg max-w-none">
                        <div class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $announcement['content'] }}</div>
                    </div>
                </div>

                @if(count($announcement['attachments']) > 0)
                    <div class="px-6 sm:px-8 lg:px-10 pb-6 sm:pb-8 lg:pb-10 border-t border-gray-100">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4 flex items-center mt-6">
                            <x-lucide-paperclip class="w-5 h-5 mr-2 text-blue-600" />
                            Lampiran ({{ count($announcement['attachments']) }})
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            @foreach($announcement['attachments'] as $attachment)
                                <div class="flex items-center p-3 sm:p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors group">
                                    <div class="flex-shrink-0 mr-3">
                                        @if(Str::endsWith($attachment, '.pdf'))
                                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                                                <x-lucide-file-text class="w-5 h-5 sm:w-6 sm:h-6" />
                                            </div>
                                        @elseif(Str::endsWith($attachment, ['.jpg', '.jpeg', '.png']))
                                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                                <x-lucide-image class="w-5 h-5 sm:w-6 sm:h-6" />
                                            </div>
                                        @elseif(Str::endsWith($attachment, ['.doc', '.docx']))
                                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                                <x-lucide-file-text class="w-5 h-5 sm:w-6 sm:h-6" />
                                            </div>
                                        @else
                                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-gray-200 text-gray-600 flex items-center justify-center">
                                                <x-lucide-file class="w-5 h-5 sm:w-6 sm:h-6" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs sm:text-sm font-bold text-gray-900 truncate">{{ $attachment }}</h4>
                                        <p class="text-[10px] sm:text-xs text-gray-500">
                                            @if(Str::endsWith($attachment, '.pdf'))
                                                Dokumen PDF
                                            @elseif(Str::endsWith($attachment, ['.jpg', '.jpeg', '.png']))
                                                Gambar
                                            @elseif(Str::endsWith($attachment, ['.doc', '.docx']))
                                                Dokumen Word
                                            @else
                                                File
                                            @endif
                                        </p>
                                    </div>
                                    <button onclick="downloadAttachment('{{ $attachment }}')" class="flex-shrink-0 ml-2 p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <x-lucide-download class="w-4 h-4 sm:w-5 sm:h-5" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4 sm:space-y-6">
            {{-- Info Card --}}
            <div class="bg-white rounded-2xl lg:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 sm:p-6 bg-gradient-to-r from-blue-600 to-blue-700">
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center">
                        <x-lucide-info class="w-5 h-5 mr-2" />
                        Informasi
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div class="p-4 sm:p-5 flex items-center justify-between">
                        <span class="text-xs sm:text-sm text-gray-500 font-medium">Jenis</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] sm:text-xs font-bold uppercase tracking-wider border {{ $typeClass }}">
                            {{ ucfirst($announcement['type']) }}
                        </span>
                    </div>
                    <div class="p-4 sm:p-5 flex items-center justify-between">
                        <span class="text-xs sm:text-sm text-gray-500 font-medium">Prioritas</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] sm:text-xs font-bold uppercase tracking-wider border {{ $priorityClass }}">
                            {{ ucfirst($announcement['priority']) }}
                        </span>
                    </div>
                    <div class="p-4 sm:p-5 flex items-center justify-between">
                        <span class="text-xs sm:text-sm text-gray-500 font-medium">Status</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] sm:text-xs font-bold uppercase tracking-wider border border-emerald-100">
                            {{ ucfirst($announcement['status']) }}
                        </span>
                    </div>
                    <div class="p-4 sm:p-5 flex items-center justify-between">
                        <span class="text-xs sm:text-sm text-gray-500 font-medium">Tanggal Publikasi</span>
                        <span class="text-xs sm:text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($announcement['published_at'])->format('d M Y') }}</span>
                    </div>
                    @if($announcement['expires_at'])
                        <div class="p-4 sm:p-5 flex items-center justify-between">
                            <span class="text-xs sm:text-sm text-gray-500 font-medium">Berakhir</span>
                            <span class="text-xs sm:text-sm font-bold text-amber-600">{{ \Carbon\Carbon::parse($announcement['expires_at'])->format('d M Y') }}</span>
                        </div>
                    @endif
                    <div class="p-4 sm:p-5 flex items-center justify-between">
                        <span class="text-xs sm:text-sm text-gray-500 font-medium">Lampiran</span>
                        <span class="text-xs sm:text-sm font-bold text-gray-900">{{ count($announcement['attachments']) }} file</span>
                    </div>
                    <div class="p-4 sm:p-5 flex items-center justify-between">
                        <span class="text-xs sm:text-sm text-gray-500 font-medium">Status Baca</span>
                        @if($announcement['read_status'])
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] sm:text-xs font-bold border border-emerald-100">
                                <x-lucide-check class="w-3 h-3 mr-1" />
                                Sudah Dibaca
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-[10px] sm:text-xs font-bold border border-amber-100">
                                <x-lucide-mail class="w-3 h-3 mr-1" />
                                Belum Dibaca
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsRead() {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2 inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses...';
    button.disabled = true;
    
    // Make AJAX call to backend
    fetch('{{ route("staff.pengumuman.mark-as-read", $announcement["id"] ?? 1) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Remove unread badges
        const unreadBadges = document.querySelectorAll('[class*="Belum Dibaca"]');
        unreadBadges.forEach(badge => badge.remove());
        
        // Update status badge in sidebar
        const statusContainers = document.querySelectorAll('.p-4.sm\\:p-5');
        statusContainers.forEach(container => {
            if (container.textContent.includes('Status Baca')) {
                const badge = container.querySelector('span:last-child');
                if (badge) {
                    badge.className = 'inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] sm:text-xs font-bold border border-emerald-100';
                    badge.innerHTML = '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Sudah Dibaca';
                }
            }
        });
        
        // Remove all mark as read buttons
        const markButtons = document.querySelectorAll('button[onclick="markAsRead()"]');
        markButtons.forEach(btn => btn.remove());
        
        showToast('Pengumuman berhasil ditandai sebagai sudah dibaca', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        button.innerHTML = originalContent;
        button.disabled = false;
        showToast('Gagal menandai pengumuman. Silakan coba lagi.', 'error');
    });
}

function downloadAttachment(filename) {
    showToast(`File ${filename} akan segera diunduh`, 'info');
}

function shareAnnouncement() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $announcement["title"] }}',
            text: '{{ Str::limit($announcement["content"], 100) }}',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            showToast('Link pengumuman berhasil disalin ke clipboard', 'success');
        });
    }
}

function showToast(message, type = 'info') {
    const bgColors = {
        'success': 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'info': 'bg-blue-50 border-blue-200 text-blue-800',
        'warning': 'bg-amber-50 border-amber-200 text-amber-800',
        'error': 'bg-rose-50 border-rose-200 text-rose-800'
    };
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl border shadow-lg ${bgColors[type]} animate-slide-in-right max-w-sm`;
    toast.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'}
            </svg>
            <span class="text-sm font-medium">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
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
@media print {
    .no-print, button, a[href] {
        display: none !important;
    }
    
    body {
        font-size: 12px;
    }
    
    .prose {
        font-size: 14px;
    }
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
@endsection