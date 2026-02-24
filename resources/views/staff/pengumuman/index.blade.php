@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24 lg:pb-0"
     x-data="{
        detailOpen: false,
        selectedAnnouncement: null,
        openDetail(announcement) {
            this.selectedAnnouncement = announcement;
            this.detailOpen = true;
        },
        closeDetail() {
            this.detailOpen = false;
            this.selectedAnnouncement = null;
        },
        markAsRead() {
            if (!this.selectedAnnouncement || this.selectedAnnouncement.read_status) return;
            const url = '{{ route('staff.pengumuman.mark-as-read', ['id' => '__ID__']) }}'.replace('__ID__', this.selectedAnnouncement.id);
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then((response) => response.json())
              .then(() => {
                this.selectedAnnouncement.read_status = true;
              });
        },
        typeLabel(type) {
            const labels = {
                tausiyah: 'Tausiyah',
                kajian: 'Kajian',
                pengumuman: 'Pengumuman',
                himbauan: 'Himbauan',
                undangan: 'Undangan'
            };
            return labels[type] || type;
        },
        priorityLabel(priority) {
            const labels = {
                low: 'Normal',
                normal: 'Normal',
                high: 'Penting',
                urgent: 'Segera'
            };
            return labels[priority] || priority;
        },
        formatDate(value) {
            if (!value) return '-';
            return new Date(value).toLocaleString('id-ID');
        }
     }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl shadow-lg shadow-blue-200 overflow-hidden">
            <div class="px-5 py-6 lg:px-6 lg:py-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-blue-100 text-xs font-semibold uppercase tracking-wide mb-2">
                            <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center hover:text-white transition-colors">
                                <x-lucide-chevron-left class="w-4 h-4 mr-1" />
                                Dashboard
                            </a>
                            <span>/</span>
                            <span>Pengumuman</span>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-white">Pengumuman</h1>
                        <p class="text-blue-100 mt-1">Informasi dan pengumuman terbaru</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white">
                        <x-lucide-megaphone class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                        <x-lucide-bell-ring class="w-3.5 h-3.5 mr-1.5" />
                        Total: {{ count($announcements) }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                        <x-lucide-pin class="w-3.5 h-3.5 mr-1.5" />
                        Pinned: {{ collect($announcements)->where('is_pinned', true)->count() }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ count($announcements) }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ collect($announcements)->where('type', 'tausiyah')->count() }}</p>
                        <p class="text-xs text-gray-500">Tausiyah</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ collect($announcements)->where('type', 'kajian')->count() }}</p>
                        <p class="text-xs text-gray-500">Kajian</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ collect($announcements)->where('is_pinned', true)->count() }}</p>
                        <p class="text-xs text-gray-500">Pinned</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Cari pengumuman..."
                           class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                
                <select id="typeFilter" class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer">
                    <option value="">Semua Jenis</option>
                    @foreach($announcementTypeOptions as $typeValue => $typeLabel)
                        <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Announcements List -->
        <div class="space-y-3">
            @forelse($announcements as $announcement)
                <button type="button"
                   @click='openDetail(@json($announcement))'
                   data-type="{{ strtolower($announcement['type']) }}"
                   class="announcement-item w-full text-left block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5 hover:shadow-md transition-all group {{ $announcement['is_pinned'] ? 'border-l-4 border-l-amber-500' : '' }}">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl 
                            @if($announcement['type'] == 'tausiyah') bg-emerald-100 text-emerald-600
                            @elseif($announcement['type'] == 'kajian') bg-blue-100 text-blue-600
                            @elseif($announcement['type'] == 'pengumuman') bg-purple-100 text-purple-600
                            @elseif($announcement['type'] == 'himbauan') bg-amber-100 text-amber-600
                            @else bg-gray-100 text-gray-600 @endif
                            flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-medium 
                                    @if($announcement['type'] == 'tausiyah') text-emerald-600 bg-emerald-50
                                    @elseif($announcement['type'] == 'kajian') text-blue-600 bg-blue-50
                                    @elseif($announcement['type'] == 'pengumuman') text-purple-600 bg-purple-50
                                    @elseif($announcement['type'] == 'himbauan') text-amber-600 bg-amber-50
                                    @else text-gray-600 bg-gray-50 @endif
                                    px-2 py-0.5 rounded">
                                    {{ $announcement['type_label'] ?? ucfirst($announcement['type']) }}
                                </span>
                                
                                @if($announcement['is_pinned'])
                                    <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        Pinned
                                    </span>
                                @endif
                            </div>

                            <h3 class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors line-clamp-1">
                                {{ $announcement['title'] }}
                            </h3>

                            <p class="text-sm text-gray-500 line-clamp-2 mt-1">
                                {{ Str::limit(strip_tags($announcement['content'] ?? ''), 100) }}
                            </p>

                            <div class="flex items-center gap-3 mt-3 text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($announcement['created_at'])->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-600 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <p class="text-gray-600 font-medium">Tidak ada pengumuman</p>
                    <p class="text-sm text-gray-500 mt-1">Belum ada pengumuman yang dipublikasikan</p>
                </div>
            @endforelse
        </div>

        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="closeDetail()"></div>
            <div class="relative w-full max-w-xl max-h-[82vh] overflow-hidden bg-white rounded-2xl shadow-2xl border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-100 flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-xs font-medium px-2 py-0.5 rounded bg-blue-50 text-blue-700" x-text="selectedAnnouncement ? (selectedAnnouncement.type_label || typeLabel(selectedAnnouncement.type)) : ''"></span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded bg-amber-50 text-amber-700" x-show="selectedAnnouncement && selectedAnnouncement.is_pinned">Pinned</span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 leading-snug" x-text="selectedAnnouncement ? selectedAnnouncement.title : ''"></h3>
                    </div>
                    <button type="button" @click="closeDetail()" class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <div class="px-4 py-3 overflow-y-auto max-h-[58vh] space-y-3">
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 rounded-lg px-3 py-2">
                            <p class="text-gray-500">Prioritas</p>
                            <p class="font-semibold text-gray-800" x-text="selectedAnnouncement ? (selectedAnnouncement.priority_label || priorityLabel(selectedAnnouncement.priority)) : '-'"></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-3 py-2">
                            <p class="text-gray-500">Status</p>
                            <p class="font-semibold text-gray-800" x-text="selectedAnnouncement ? (selectedAnnouncement.read_status ? 'Sudah dibaca' : 'Belum dibaca') : '-'"></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-3 py-2 col-span-2">
                            <p class="text-gray-500">Dibuat Oleh</p>
                            <p class="font-semibold text-gray-800" x-text="selectedAnnouncement ? selectedAnnouncement.created_by : '-'"></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-3 py-2">
                            <p class="text-gray-500">Dibuat</p>
                            <p class="font-semibold text-gray-800" x-text="selectedAnnouncement ? formatDate(selectedAnnouncement.created_at) : '-'"></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-3 py-2">
                            <p class="text-gray-500">Berlaku Sampai</p>
                            <p class="font-semibold text-gray-800" x-text="selectedAnnouncement ? formatDate(selectedAnnouncement.expires_at) : '-'"></p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap" x-text="selectedAnnouncement ? selectedAnnouncement.content : ''"></p>

                    <div x-show="selectedAnnouncement && selectedAnnouncement.attachments && selectedAnnouncement.attachments.length">
                        <h4 class="text-sm font-bold text-gray-900 mb-2">Lampiran</h4>
                        <div class="space-y-2">
                            <template x-for="file in (selectedAnnouncement ? selectedAnnouncement.attachments : [])" :key="file">
                                <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-700" x-text="file"></div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-between gap-3">
                    <span class="text-sm font-medium text-emerald-700" x-show="selectedAnnouncement && selectedAnnouncement.read_status">Sudah dibaca</span>
                    <span class="text-sm font-medium text-amber-700" x-show="selectedAnnouncement && !selectedAnnouncement.read_status">Belum dibaca</span>
                    <button type="button" @click="markAsRead()" x-show="selectedAnnouncement && !selectedAnnouncement.read_status" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        Tandai Sudah Dibaca
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Simple search filter
    document.getElementById('searchInput')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.announcement-item');
        
        items.forEach(item => {
            const title = item.querySelector('h3')?.textContent.toLowerCase() || '';
            const content = item.querySelector('p')?.textContent.toLowerCase() || '';
            
            if (title.includes(searchTerm) || content.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Type filter
    document.getElementById('typeFilter')?.addEventListener('change', function(e) {
        const type = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.announcement-item');
        
        items.forEach(item => {
            const itemType = (item.dataset.type || '').toLowerCase();
            
            if (!type || itemType.includes(type)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>
@endpush
