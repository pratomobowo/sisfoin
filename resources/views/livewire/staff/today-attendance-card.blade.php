<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sm:p-6 mb-6 relative overflow-hidden">
    <div class="grid grid-cols-2 lg:flex lg:flex-row items-stretch lg:items-center gap-4 sm:gap-6 lg:gap-8">
        
        <!-- BLOCK 1: WAKTU & SHIFT -->
        <div class="col-span-2 lg:col-span-1 flex items-center gap-3 sm:gap-4 min-w-0 lg:min-w-[240px] mb-2 lg:mb-0">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center border border-blue-100 flex-shrink-0">
                <x-lucide-clock class="w-5 h-5 sm:w-6 sm:h-6" />
            </div>
            <div class="flex flex-col min-w-0">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu Server</span>
                <span class="text-lg sm:text-2xl font-bold text-gray-900 leading-tight">{{ now()->format('H:i') }}</span>
                <div class="flex items-center gap-1.5 mt-0.5 sm:mt-1">
                    <span class="px-1.5 py-0.5 bg-blue-600 text-white text-xs font-semibold rounded uppercase tracking-wide">
                        {{ $shift->name ?? 'Normal' }}
                    </span>
                    <span class="text-xs text-gray-500 font-medium truncate">
                        {{ substr($shift->start_time ?? '08:00', 0, 5) }} - {{ substr($shift->end_time ?? '16:30', 0, 5) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- BLOCK 2: ABSEN MASUK -->
        <div class="flex flex-col pl-3 sm:pl-5 border-l-2 {{ $attendance && $attendance->check_in_time ? 'border-emerald-500' : 'border-gray-100' }}">
            <span class="text-xs font-semibold {{ $attendance && $attendance->check_in_time ? 'text-gray-500' : 'text-gray-400' }} uppercase tracking-wide">Jam Masuk</span>
            <span class="text-lg sm:text-2xl font-bold {{ $attendance && $attendance->check_in_time ? 'text-gray-900' : 'text-gray-200' }}">
                {{ $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '--:--' }}
            </span>
            @if($attendance && $attendance->check_in_time)
                <div class="flex items-center mt-0.5 text-emerald-600">
                    <span class="text-xs font-semibold uppercase tracking-wide">Tercatat</span>
                </div>
            @else
                <span class="text-xs font-medium text-gray-300 uppercase tracking-wide mt-0.5">Belum Absen</span>
            @endif
        </div>

        <!-- BLOCK 3: ABSEN PULANG -->
        <div class="flex flex-col pl-3 sm:pl-5 border-l-2 {{ $attendance && $attendance->check_out_time ? 'border-indigo-500' : 'border-gray-100' }}">
            <span class="text-xs font-semibold {{ $attendance && $attendance->check_out_time ? 'text-gray-500' : 'text-gray-400' }} uppercase tracking-wide">Jam Pulang</span>
            <span class="text-lg sm:text-2xl font-bold {{ $attendance && $attendance->check_out_time ? 'text-gray-900' : 'text-gray-200' }}">
                {{ $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '--:--' }}
            </span>
            @if($attendance && $attendance->check_out_time)
                <div class="flex items-center mt-0.5 text-indigo-600">
                    <span class="text-xs font-semibold uppercase tracking-wide">Tercatat</span>
                </div>
            @else
                <span class="text-xs font-medium text-gray-300 uppercase tracking-wide mt-0.5">Belum Absen</span>
            @endif
        </div>

        <!-- BLOCK 4: STATUS BADGE -->
        <div class="col-span-2 lg:col-span-1 flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-center lg:ml-auto pt-3 lg:pt-0 border-t lg:border-t-0 lg:border-l lg:pl-8 border-gray-100">
            @if($attendance && $attendance->status)
                @php
                    $statusColors = [
                        'on_time' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                        'early_arrival' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                        'late' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        'incomplete' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
                        'absent' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                    ];
                    $statusClass = $statusColors[$attendance->status] ?? 'bg-gray-50 text-gray-700 ring-gray-600/20';
                @endphp
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide lg:mb-2">Status</span>
                <div class="inline-flex items-center rounded-lg px-2.5 py-1 sm:px-4 sm:py-2 text-xs font-semibold uppercase tracking-wide ring-1 ring-inset {{ $statusClass }}">
                    {{ $attendance->status_label }}
                </div>
            @else
                <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between w-full lg:w-auto opacity-40">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide lg:mb-1">Status Kehadiran</span>
                    <span class="text-xs font-semibold text-gray-400 italic">Data Belum Tersedia</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Progress Tracker (Bottom Edge) -->
    @php
        $progress = 0;
        if ($attendance) {
            if ($attendance->check_in_time) $progress += 50;
            if ($attendance->check_out_time) $progress += 50;
        }
    @endphp
    <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-100">
        <div class="h-full bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-600 transition-all duration-1000 ease-in-out" 
             style="width: {{ $progress }}%"></div>
    </div>
</div>
