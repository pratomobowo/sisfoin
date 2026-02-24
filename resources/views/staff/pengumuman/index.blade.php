@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24 lg:pb-0">
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
                    <option value="tausiyah">Tausiyah</option>
                    <option value="kajian">Kajian</option>
                    <option value="pengumuman">Pengumuman</option>
                    <option value="himbauan">Himbauan</option>
                    <option value="undangan">Undangan</option>
                </select>
            </div>
        </div>

        <!-- Announcements List -->
        <div class="space-y-3">
            @forelse($announcements as $announcement)
                <a href="{{ route('staff.pengumuman.show', $announcement['id']) }}" 
                   class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5 hover:shadow-md transition-all group {{ $announcement['is_pinned'] ? 'border-l-4 border-l-amber-500' : '' }}">
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
                                    {{ ucfirst($announcement['type']) }}
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
                </a>
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

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Simple search filter
    document.getElementById('searchInput')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.space-y-3 > a');
        
        items.forEach(item => {
            const title = item.querySelector('h3')?.textContent.toLowerCase() || '';
            const content = item.querySelector('p')?.textContent.toLowerCase() || '';
            
            if (title.includes(searchTerm) || content.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Type filter
    document.getElementById('typeFilter')?.addEventListener('change', function(e) {
        const type = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.space-y-3 > a');
        
        items.forEach(item => {
            const itemType = item.querySelector('.text-xs.font-medium')?.textContent.toLowerCase() || '';
            
            if (!type || itemType.includes(type)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>
@endpush
