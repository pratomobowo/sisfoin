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
                            <span>Penggajian</span>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-white">Slip Gaji</h1>
                        <p class="text-blue-100 mt-1">Lihat dan unduh slip gaji Anda</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white">
                        <x-lucide-wallet class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                        <x-lucide-file-text class="w-3.5 h-3.5 mr-1.5" />
                        {{ $availableSlips ?? 0 }} slip tersedia
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                        <x-lucide-badge-indian-rupee class="w-3.5 h-3.5 mr-1.5" />
                        Total: Rp {{ number_format($totalGajiBersih ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
        
        @if($totalSlips > 0)
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Gaji Terakhir -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Terbaru</span>
                    </div>
                    <p class="text-lg lg:text-xl font-bold text-gray-800 truncate">
                        Rp {{ number_format($latestGajiBersih ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Gaji Terakhir {{ $latestGajiPeriode ? '(' . $latestGajiPeriode . ')' : '' }}</p>
                </div>

                <!-- Potongan Terakhir -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-rose-600 bg-rose-50 px-2 py-1 rounded-full">Terbaru</span>
                    </div>
                    <p class="text-lg lg:text-xl font-bold text-gray-800 truncate">
                        Rp {{ number_format($latestTotalPotongan ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Potongan Terakhir</p>
                </div>

                <!-- Honor Terakhir -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Terbaru</span>
                    </div>
                    <p class="text-lg lg:text-xl font-bold text-gray-800 truncate">
                        Rp {{ number_format($latestTotalHonor ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Honor Terakhir</p>
                </div>

                <!-- Jumlah Slip -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">Slip</span>
                    </div>
                    <p class="text-lg lg:text-xl font-bold text-gray-800">{{ $totalSlips }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Slip</p>
                </div>
            </div>

            <!-- Search -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <form action="{{ route('staff.penggajian.index') }}" method="GET" class="flex gap-3">
                    <div class="flex-1 relative">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $search }}" 
                               placeholder="Cari periode..."
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Slip List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 lg:p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800">Daftar Slip Gaji</h2>
                    <p class="text-sm text-gray-500">{{ $availableSlips }} slip tersedia</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($slipGaji as $slip)
                        <div class="p-4 lg:p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <!-- Icon -->
                                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>

                                    <!-- Info -->
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $slip['period_name'] ?? $slip['period'] }}</p>
                                        <p class="text-sm text-gray-500">
                                            Gaji Bersih: <span class="font-medium text-emerald-600">Rp {{ number_format($slip['gaji_bersih'], 0, ',', '.') }}</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Download Button -->
                                @if($slip['can_download'])
                                    <a href="#" class="flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl text-sm font-medium hover:bg-blue-100 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Unduh
                                    </a>
                                @else
                                    <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded-xl text-sm">Belum Tersedia</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-gray-600 font-medium">Tidak ada slip gaji</p>
                            <p class="text-sm text-gray-500 mt-1">Slip gaji akan muncul di sini</p>
                        </div>
                    @endforelse
                </div>

                @if($pagination)
                    <div class="p-4 border-t border-gray-100">
                        {{ $pagination->links() }}
                    </div>
                @endif
            </div>

        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Belum Ada Slip Gaji</h3>
                <p class="text-gray-500 mb-6">Slip gaji Anda akan muncul di sini setelah diproses oleh bagian SDM.</p>
                
                <div class="bg-blue-50 rounded-xl p-4 text-left">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-800">Informasi</p>
                            <p class="text-sm text-blue-600 mt-1">Jika Anda merasa sudah seharusnya memiliki slip gaji, silakan hubungi bagian SDM.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
