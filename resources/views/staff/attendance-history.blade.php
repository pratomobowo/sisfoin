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
                            <span>Absensi</span>
                        </div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-white">Riwayat Absensi</h1>
                        <p class="text-blue-100 mt-1">Pantau kehadiran dan ketidakhadiran Anda</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white">
                        <x-lucide-calendar-check class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                        <x-lucide-calendar-range class="w-3.5 h-3.5 mr-1.5" />
                        {{ $months[$month] ?? 'Bulan' }} {{ $year }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 text-white text-xs font-semibold">
                        <x-lucide-check-circle class="w-3.5 h-3.5 mr-1.5" />
                        Hadir: {{ $summary['present'] ?? 0 }} hari
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Month/Year Filter -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <form action="{{ route('staff.attendance.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                    <select name="month" onchange="this.form.submit()" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none cursor-pointer">
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <select name="year" onchange="this.form.submit()" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none cursor-pointer">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Hadir -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Hadir</span>
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-800">{{ $summary['present'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Hari</p>
            </div>

            <!-- Terlambat -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Telat</span>
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-800">{{ $summary['late'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Kali</p>
            </div>

            <!-- Tidak Lengkap -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">Tdk Lengkap</span>
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-800">{{ $summary['incomplete'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Hari</p>
            </div>

            <!-- Alpa -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-rose-600 bg-rose-50 px-2 py-1 rounded-full">Alpa</span>
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-800">{{ $summary['absent'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Hari</p>
            </div>
        </div>

        <!-- Detail Kehadiran: 2 Kolom -->
        @php
            $riwayat = collect($history ?? [])->sortByDesc('date')->values();
        @endphp
        <div>
            <section>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base lg:text-lg font-bold text-gray-800">Detail Kehadiran</h2>
                        <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg">Urutan: Terbaru</span>
                    </div>

                    @if($riwayat->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($riwayat as $attendance)
                                <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-4 hover:border-blue-200 hover:bg-blue-50/30 transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">
                                                {{ \Carbon\Carbon::parse($attendance['date'])->locale('id')->isoFormat('ddd') }}
                                            </p>
                                            <p class="text-lg font-bold text-gray-900">
                                                {{ \Carbon\Carbon::parse($attendance['date'])->locale('id')->isoFormat('D MMMM YYYY') }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold
                                            @if($attendance['status'] == 'present' || $attendance['status'] == 'on_time' || $attendance['status'] == 'early_arrival') bg-emerald-100 text-emerald-700
                                            @elseif($attendance['status'] == 'late') bg-amber-100 text-amber-700
                                            @elseif($attendance['status'] == 'absent') bg-rose-100 text-rose-700
                                            @else bg-gray-200 text-gray-700 @endif">
                                            @if($attendance['status'] == 'present' || $attendance['status'] == 'on_time')
                                                Hadir
                                            @elseif($attendance['status'] == 'early_arrival')
                                                Datang Lebih Awal
                                            @elseif($attendance['status'] == 'late')
                                                Terlambat
                                            @elseif($attendance['status'] == 'absent')
                                                Tidak Hadir
                                            @elseif($attendance['status'] == 'permission')
                                                Izin
                                            @else
                                                {{ ucfirst($attendance['status'] ?? '-') }}
                                            @endif
                                        </span>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <div class="bg-white rounded-xl border border-gray-100 p-2.5">
                                            <p class="text-[10px] font-semibold uppercase text-gray-500">Jam Masuk</p>
                                            <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $attendance['check_in'] ?: '-' }}</p>
                                        </div>
                                        <div class="bg-white rounded-xl border border-gray-100 p-2.5">
                                            <p class="text-[10px] font-semibold uppercase text-gray-500">Jam Pulang</p>
                                            <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $attendance['check_out'] ?: '-' }}</p>
                                        </div>
                                    </div>

                                    @php
                                        $notesText = trim((string) ($attendance['notes'] ?? ''));

                                        // For legacy auto-generated late notes (e.g. "Terlambat 6 menit"
                                        // or "Terlambat 70 menit, Multiple scans: 3 kali"),
                                        // prefer computed metric and keep only the extra suffix note.
                                        if (!is_null($attendance['late_minutes'])) {
                                            if (preg_match('/^terlambat\s*-?\d+\s*menit\s*(?:,\s*(.*))?$/i', $notesText, $matches)) {
                                                $notesText = trim((string) ($matches[1] ?? ''));
                                            }
                                        }

                                        $normalizedNotes = function_exists('mb_strtolower')
                                            ? mb_strtolower($notesText)
                                            : strtolower($notesText);

                                        $performanceNotes = [];

                                        if (!is_null($attendance['late_minutes'])) {
                                            $performanceNotes[] = 'Terlambat '.$attendance['late_minutes'].' menit';
                                        }

                                        if (!is_null($attendance['overtime_hours']) && !str_contains($normalizedNotes, 'lembur')) {
                                            $performanceNotes[] = 'Lembur '.number_format($attendance['overtime_hours'], 2, ',', '.').' jam';
                                        }
                                    @endphp

                                    @if(!empty($notesText) || !empty($performanceNotes))
                                        <div class="mt-3 text-xs text-gray-600 bg-white border border-gray-100 rounded-xl p-2.5">
                                            <span class="font-semibold">Catatan:</span>
                                            @if(!empty($notesText))
                                                {{ $notesText }}
                                            @endif
                                            @if(!empty($performanceNotes))
                                                @if(!empty($notesText)) · @endif
                                                {{ implode(' · ', $performanceNotes) }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-gray-600 font-medium">Belum ada data absensi</p>
                            <p class="text-sm text-gray-500 mt-1">Data absensi akan muncul di sini</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>

    </div>
</div>
@endsection
