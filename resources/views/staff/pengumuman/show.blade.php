@extends('layouts.staff')

@section('page-title', $announcement['title'])

@section('content')
<div class="space-y-4">

    @php
        $typeBadges = [
            'tausiyah' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Tausiyah'],
            'kajian' => ['bg-cyan-50 text-cyan-700 border-cyan-200', 'Kajian'],
            'pengumuman' => ['bg-blue-50 text-blue-700 border-blue-200', 'Pengumuman'],
            'himbauan' => ['bg-amber-50 text-amber-700 border-amber-200', 'Himbauan'],
            'undangan' => ['bg-purple-50 text-purple-700 border-purple-200', 'Undangan'],
        ];
        $typeInfo = $typeBadges[$announcement['type']] ?? ['bg-gray-50 text-gray-700 border-gray-200', ucfirst($announcement['type'])];
        
        $priorityBadges = [
            'low' => ['bg-gray-50 text-gray-700 border-gray-200', 'Normal'],
            'normal' => ['bg-blue-50 text-blue-700 border-blue-200', 'Normal'],
            'high' => ['bg-amber-50 text-amber-700 border-amber-200', 'Penting'],
            'urgent' => ['bg-rose-50 text-rose-700 border-rose-200', 'Segera'],
        ];
        $priorityInfo = $priorityBadges[$announcement['priority']] ?? ['bg-gray-50 text-gray-700 border-gray-200', ucfirst($announcement['priority'])];
    @endphp

    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl shadow-lg shadow-blue-200 overflow-hidden">
        <div class="px-5 py-6 lg:px-6 lg:py-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-blue-100 text-xs font-semibold uppercase tracking-wide mb-2">
                        <a href="{{ route('staff.pengumuman.index') }}" class="inline-flex items-center hover:text-white transition-colors">
                            <x-lucide-chevron-left class="w-4 h-4 mr-1" />
                            Pengumuman
                        </a>
                        <span>/</span>
                        <span>Detail</span>
                    </div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-white leading-tight">{{ $announcement['title'] }}</h1>
                    <p class="text-blue-100 mt-1">Detail informasi pengumuman untuk staff</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.print()" class="p-2.5 bg-white/20 text-white rounded-xl hover:bg-white/30 transition-all">
                        <x-lucide-printer class="w-4 h-4" />
                    </button>
                    <button onclick="shareAnnouncement()" class="p-2.5 bg-white/20 text-white rounded-xl hover:bg-white/30 transition-all">
                        <x-lucide-share-2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">{{ $typeInfo[1] }}</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">{{ $priorityInfo[1] }}</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                    {{ $announcement['read_status'] ? 'Sudah Dibaca' : 'Belum Dibaca' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header Section --}}
        <div class="p-5 border-b border-gray-100">
            {{-- Badges Row --}}
            <div class="flex flex-wrap items-center gap-2 mb-4">
                @if($announcement['is_pinned'])
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
                        <x-lucide-pin class="w-3 h-3 mr-1.5" />
                        Disematkan
                    </span>
                @endif
                @if(!$announcement['read_status'])
                    <span class="unread-badge inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200">
                        <div class="w-2 h-2 rounded-full bg-blue-600 mr-1.5 animate-pulse"></div>
                        Belum Dibaca
                    </span>
                @endif
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $typeInfo[0] }}">
                    {{ $typeInfo[1] }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $priorityInfo[0] }}">
                    {{ $priorityInfo[1] }}
                </span>
            </div>

            {{-- Title --}}
            <h1 class="text-xl font-bold text-gray-900 leading-tight mb-4">
                {{ $announcement['title'] }}
            </h1>

            {{-- Meta Info --}}
            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                <div class="flex items-center">
                    <x-lucide-user class="w-4 h-4 mr-1.5 text-gray-400" />
                    <span class="font-medium">{{ $announcement['created_by'] }}</span>
                </div>
                <div class="flex items-center">
                    <x-lucide-calendar class="w-4 h-4 mr-1.5 text-gray-400" />
                    <span>{{ \Carbon\Carbon::parse($announcement['published_at'])->format('d M Y, H:i') }}</span>
                </div>
                @if($announcement['expires_at'])
                    <div class="flex items-center">
                        <x-lucide-clock class="w-4 h-4 mr-1.5 text-amber-500" />
                        <span class="text-amber-600">Berlaku sampai {{ \Carbon\Carbon::parse($announcement['expires_at'])->format('d M Y') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Content Section --}}
        <div class="p-5">
            <div class="prose prose-sm max-w-none">
                <div class="text-gray-700 leading-relaxed whitespace-pre-wrap text-sm">{{ $announcement['content'] }}</div>
            </div>
        </div>

        {{-- Attachments Section --}}
        @if(count($announcement['attachments']) > 0)
            <div class="px-5 pb-5 border-t border-gray-100 pt-5">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center">
                    <x-lucide-paperclip class="w-4 h-4 mr-2 text-blue-600" />
                    Lampiran ({{ count($announcement['attachments']) }})
                </h3>
                <div class="grid grid-cols-1 gap-3">
                    @foreach($announcement['attachments'] as $attachment)
                        <div class="flex items-center p-3 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors group">
                            <div class="flex-shrink-0 mr-3">
                                @if(Str::endsWith($attachment, '.pdf'))
                                    <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                                        <x-lucide-file-text class="w-5 h-5" />
                                    </div>
                                @elseif(Str::endsWith($attachment, ['.jpg', '.jpeg', '.png']))
                                    <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                        <x-lucide-image class="w-5 h-5" />
                                    </div>
                                @elseif(Str::endsWith($attachment, ['.doc', '.docx']))
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                        <x-lucide-file-text class="w-5 h-5" />
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-200 text-gray-600 flex items-center justify-center">
                                        <x-lucide-file class="w-5 h-5" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0 mr-3">
                                <h4 class="text-xs font-bold text-gray-900 truncate">{{ $attachment }}</h4>
                                <p class="text-[10px] text-gray-500">
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
                            <button onclick="downloadAttachment('{{ $attachment }}')" class="flex-shrink-0 p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                <x-lucide-download class="w-4 h-4" />
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Footer Actions --}}
        @if(!$announcement['read_status'])
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                <button onclick="markAsRead()" class="w-full inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                    <x-lucide-check class="w-4 h-4 mr-2" />
                    Tandai Sudah Dibaca
                </button>
            </div>
        @else
            <div class="px-5 py-4 bg-emerald-50 border-t border-emerald-100">
                <div class="flex items-center justify-center text-emerald-700 text-sm font-bold">
                    <x-lucide-check-circle class="w-5 h-5 mr-2" />
                    Sudah Dibaca
                </div>
            </div>
        @endif
    </div>

    {{-- Additional Info Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-900 flex items-center">
                <x-lucide-info class="w-4 h-4 mr-2 text-gray-400" />
                Informasi Detail
            </h3>
        </div>
        <div class="p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Status</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                    {{ ucfirst($announcement['status']) }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Prioritas</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $priorityInfo[0] }}">
                    {{ $priorityInfo[1] }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Lampiran</span>
                <span class="text-sm font-bold text-gray-900">{{ count($announcement['attachments']) }} file</span>
            </div>
        </div>
    </div>
</div>

<script>
function markAsRead() {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<x-lucide-loader-2 class="w-4 h-4 animate-spin mr-2 inline-block" />Memproses...';
    button.disabled = true;
    
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
        const unreadBadges = document.querySelectorAll('.unread-badge');
        unreadBadges.forEach(badge => badge.remove());
        
        // Replace button with success message
        const footerDiv = button.closest('div');
        footerDiv.className = 'px-5 py-4 bg-emerald-50 border-t border-emerald-100';
        footerDiv.innerHTML = `
            <div class="flex items-center justify-center text-emerald-700 text-sm font-bold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Sudah Dibaca
            </div>
        `;
        
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

@media print {
    .no-print, button, a[href] {
        display: none !important;
    }
}
</style>
@endsection
