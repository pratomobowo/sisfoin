<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    {{-- Breadcrumb --}}
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
                    Riwayat Absensi
                </span>
            </li>
        </ol>
    </nav>

    {{-- Header Section --}}
    <div class="bg-white rounded-2xl lg:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-6 lg:p-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 sm:gap-6">
            <div class="space-y-1 sm:space-y-2">
                <div class="inline-flex items-center px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-blue-50 text-blue-600 text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-1 sm:mb-2">
                    Layanan Mandiri
                </div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">Riwayat Absensi</h1>
                <p class="text-gray-500 text-xs sm:text-sm max-w-md leading-relaxed">
                    Pantau kehadiran Anda setiap bulan. Data diperbarui otomatis dari sistem mesin absensi.
                </p>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full md:w-auto">
                <div class="relative group flex-1 sm:flex-none">
                    <select wire:model.live="month" class="w-full sm:w-auto pl-3 sm:pl-4 pr-8 sm:pr-10 py-2 sm:py-2.5 bg-gray-50 border-none rounded-xl sm:rounded-2xl text-xs sm:text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer appearance-none shadow-sm group-hover:bg-gray-100">
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                        <x-lucide-chevron-down class="w-3 h-3 sm:w-4 sm:h-4" />
                    </div>
                </div>

                <div class="relative group flex-1 sm:flex-none">
                    <select wire:model.live="year" class="w-full sm:w-auto pl-3 sm:pl-4 pr-8 sm:pr-10 py-2 sm:py-2.5 bg-gray-50 border-none rounded-xl sm:rounded-2xl text-xs sm:text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer appearance-none shadow-sm group-hover:bg-gray-100">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                        <x-lucide-chevron-down class="w-3 h-3 sm:w-4 sm:h-4" />
                    </div>
                </div>

                <a href="{{ route('dashboard') }}" class="p-2 sm:p-2.5 bg-white border border-gray-200 rounded-xl sm:rounded-2xl text-gray-500 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm">
                    <x-lucide-layout-dashboard class="w-4 h-4 sm:w-5 sm:h-5" />
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
        <!-- Present Card -->
        <div class="bg-white p-4 sm:p-5 lg:p-6 rounded-2xl lg:rounded-3xl shadow-sm border border-gray-100 flex items-center space-x-3 sm:space-x-4 lg:space-x-5 transition-transform hover:scale-[1.02]">
            <div class="p-2.5 sm:p-3 lg:p-4 bg-emerald-50 text-emerald-600 rounded-xl lg:rounded-2xl shadow-inner flex-shrink-0">
                <x-lucide-check-circle class="w-5 h-5 sm:w-6 sm:h-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider sm:tracking-widest mb-0.5 sm:mb-1">Hadir</p>
                <div class="flex items-baseline space-x-1">
                    <span class="text-xl sm:text-2xl font-black text-gray-900">{{ $summary['present'] }}</span>
                    <span class="text-[10px] sm:text-xs text-gray-400 font-medium">Hari</span>
                </div>
            </div>
        </div>

        <!-- Late Card -->
        <div class="bg-white p-4 sm:p-5 lg:p-6 rounded-2xl lg:rounded-3xl shadow-sm border border-gray-100 flex items-center space-x-3 sm:space-x-4 lg:space-x-5 transition-transform hover:scale-[1.02]">
            <div class="p-2.5 sm:p-3 lg:p-4 bg-amber-50 text-amber-600 rounded-xl lg:rounded-2xl shadow-inner flex-shrink-0">
                <x-lucide-clock class="w-5 h-5 sm:w-6 sm:h-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider sm:tracking-widest mb-0.5 sm:mb-1 truncate">Terlambat</p>
                <div class="flex items-baseline space-x-1">
                    <span class="text-xl sm:text-2xl font-black text-amber-600">{{ $summary['late'] }}</span>
                    <span class="text-[10px] sm:text-xs text-gray-400 font-medium">Kali</span>
                </div>
            </div>
        </div>

        <!-- Incomplete Card -->
        <div class="bg-white p-4 sm:p-5 lg:p-6 rounded-2xl lg:rounded-3xl shadow-sm border border-gray-100 flex items-center space-x-3 sm:space-x-4 lg:space-x-5 transition-transform hover:scale-[1.02]">
            <div class="p-2.5 sm:p-3 lg:p-4 bg-purple-50 text-purple-600 rounded-xl lg:rounded-2xl shadow-inner flex-shrink-0">
                <x-lucide-alert-triangle class="w-5 h-5 sm:w-6 sm:h-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider sm:tracking-widest mb-0.5 sm:mb-1 truncate">Tdk Lengkap</p>
                <div class="flex items-baseline space-x-1">
                    <span class="text-xl sm:text-2xl font-black text-purple-600">{{ $summary['incomplete'] }}</span>
                    <span class="text-[10px] sm:text-xs text-gray-400 font-medium">Hari</span>
                </div>
            </div>
        </div>

        <!-- Absent Card -->
        <div class="bg-white p-4 sm:p-5 lg:p-6 rounded-2xl lg:rounded-3xl shadow-sm border border-gray-100 flex items-center space-x-3 sm:space-x-4 lg:space-x-5 transition-transform hover:scale-[1.02]">
            <div class="p-2.5 sm:p-3 lg:p-4 bg-rose-50 text-rose-600 rounded-xl lg:rounded-2xl shadow-inner flex-shrink-0">
                <x-lucide-x-circle class="w-5 h-5 sm:w-6 sm:h-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider sm:tracking-widest mb-0.5 sm:mb-1">Alpa</p>
                <div class="flex items-baseline space-x-1">
                    <span class="text-xl sm:text-2xl font-black text-rose-600">{{ $summary['absent'] }}</span>
                    <span class="text-[10px] sm:text-xs text-gray-400 font-medium">Hari</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Log Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center">
                <x-lucide-list class="w-5 h-5 mr-3 text-blue-600" />
                Catatan Kehadiran Harian
            </h3>
            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-2 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100">
                    <div class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Live Updates</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em]">Tanggal & Hari</th>
                        <th class="px-8 py-4 text-center text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em]">Jam Masuk</th>
                        <th class="px-8 py-4 text-center text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em]">Jam Keluar</th>
                        <th class="px-8 py-4 text-center text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em]">Status</th>
                        <th class="px-8 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em]">Informasi / Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($history as $item)
                        <tr class="hover:bg-blue-50/10 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl flex flex-col items-center justify-center 
                                        {{ $item['is_weekend'] || $item['is_holiday'] ? 'bg-gray-100 text-gray-400' : 'bg-blue-50 text-blue-600' }}">
                                        <span class="text-lg font-black leading-none">{{ explode(' ', $item['formatted_date'])[0] }}</span>
                                        <span class="text-[9px] font-bold uppercase">{{ substr($item['day_name'], 0, 3) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ $item['formatted_date'] }}</p>
                                        <p class="text-xs font-medium text-gray-500">{{ $item['day_name'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                @if($item['check_in'] && $item['check_in'] !== '-')
                                    <span class="inline-flex flex-col">
                                        <span class="text-sm font-black text-gray-900">{{ $item['check_in'] }}</span>
                                        <span class="text-[8px] font-bold text-emerald-500 uppercase tracking-tighter">Recorded</span>
                                    </span>
                                @else
                                    <span class="text-gray-300">--:--</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-center">
                                @if($item['check_out'] && $item['check_out'] !== '-')
                                    <span class="inline-flex flex-col">
                                        <span class="text-sm font-black text-gray-900">{{ $item['check_out'] }}</span>
                                        <span class="text-[8px] font-bold text-emerald-500 uppercase tracking-tighter">Recorded</span>
                                    </span>
                                @else
                                    <span class="text-gray-300">--:--</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-center">
                                @php
                                    $badgeClasses = [
                                        'blue' => 'bg-blue-50 text-blue-700 ring-blue-600/10',
                                        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                                        'yellow' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
                                        'red' => 'bg-rose-50 text-rose-700 ring-rose-600/10',
                                        'orange' => 'bg-orange-50 text-orange-700 ring-orange-600/10',
                                        'purple' => 'bg-purple-50 text-purple-700 ring-purple-600/10',
                                        'gray' => 'bg-gray-50 text-gray-600 ring-gray-600/10',
                                    ];
                                    $color = $item['status_badge'] ?? 'gray';
                                    $class = $badgeClasses[$color] ?? $badgeClasses['gray'];
                                @endphp
                                <span class="inline-flex items-center rounded-xl px-3 py-1 text-[11px] font-bold ring-1 ring-inset {{ $class }}">
                                    {{ $item['status_label'] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                @if($item['is_holiday'])
                                    <div class="flex items-center text-rose-500 space-x-2">
                                        <x-lucide-party-popper class="w-4 h-4" />
                                        <span class="text-xs font-bold italic">{{ $item['holiday_name'] }}</span>
                                    </div>
                                @elseif($item['notes'])
                                    <p class="text-xs font-medium text-gray-500 bg-gray-50 px-3 py-2 rounded-xl border border-dotted border-gray-200 inline-block">
                                        {{ $item['notes'] }}
                                    </p>
                                @else
                                    <span class="text-[10px] text-gray-300 italic font-medium">Tidak ada catatan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <x-lucide-calendar-x class="w-10 h-10 text-gray-300" />
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-900">Data Tidak Ditemukan</h4>
                                    <p class="text-gray-500 text-sm">Belum ada data absensi untuk periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Legend Section -->
        <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-50">
            <div class="flex flex-wrap items-center gap-6">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mr-2">Legenda Status:</span>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm shadow-emerald-200"></div>
                    <span class="text-[10px] font-bold text-gray-600 tracking-tight">Hadir Tepat Waktu</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-amber-500 shadow-sm shadow-amber-200"></div>
                    <span class="text-[10px] font-bold text-gray-600 tracking-tight">Terlambat</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-rose-500 shadow-sm shadow-rose-200"></div>
                    <span class="text-[10px] font-bold text-gray-600 tracking-tight">Tidak Hadir (Alpa)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-purple-500 shadow-sm shadow-purple-200"></div>
                    <span class="text-[10px] font-bold text-gray-600 tracking-tight">Absen Tidak Lengkap</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-gray-400 shadow-sm shadow-gray-100"></div>
                    <span class="text-[10px] font-bold text-gray-600 tracking-tight">Libur / Akhir Pekan</span>
                </div>
            </div>
        </div>
    </div>
</div>
