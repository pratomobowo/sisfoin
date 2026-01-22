<div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 mb-8 relative overflow-hidden">
    <div class="flex flex-col lg:flex-row justify-between items-center gap-8 lg:gap-12">
        
        <!-- BLOCK 1: WAKTU & SHIFT (Left) -->
        <div class="flex items-center gap-6 w-full lg:w-auto">
            <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shadow-sm border border-blue-100/50 flex-shrink-0">
                <x-lucide-clock class="w-8 h-8" />
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Waktu Server</span>
                <span class="text-4xl font-black text-gray-900 leading-none tracking-tight">{{ now()->format('H:i') }}</span>
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-2 py-0.5 bg-blue-600 text-white text-[9px] font-bold rounded-md uppercase tracking-wide">
                        {{ $shift->name ?? 'Normal' }}
                    </span>
                    <span class="text-[10px] font-bold text-gray-400">
                        {{ substr($shift->start_time ?? '08:00', 0, 5) }} - {{ substr($shift->end_time ?? '16:30', 0, 5) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- DIVIDER (Visible on Desktop) -->
        <div class="hidden lg:block w-px h-16 bg-gray-100"></div>

        <!-- BLOCK 2: ABSEN MASUK (Middle) -->
        <div class="flex items-center gap-5 w-full lg:w-auto">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors
                {{ $attendance && $attendance->check_in_time ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-gray-50 text-gray-300 border border-gray-100' }}">
                <x-lucide-log-in class="w-7 h-7" />
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jam Masuk</span>
                <span class="text-3xl font-black {{ $attendance && $attendance->check_in_time ? 'text-gray-900' : 'text-gray-200' }}">
                    {{ $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '--:--' }}
                </span>
                @if($attendance && $attendance->check_in_time)
                    <span class="text-[9px] font-bold text-emerald-600 flex items-center mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Tercatat
                    </span>
                @endif
            </div>
        </div>

        <!-- DIVIDER (Visible on Desktop) -->
        <div class="hidden lg:block w-px h-16 bg-gray-100"></div>

        <!-- BLOCK 3: ABSEN PULANG (Middle) -->
        <div class="flex items-center gap-5 w-full lg:w-auto">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors
                {{ $attendance && $attendance->check_out_time ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-gray-50 text-gray-300 border border-gray-100' }}">
                <x-lucide-log-out class="w-7 h-7" />
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jam Pulang</span>
                <span class="text-3xl font-black {{ $attendance && $attendance->check_out_time ? 'text-gray-900' : 'text-gray-200' }}">
                    {{ $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '--:--' }}
                </span>
                @if($attendance && $attendance->check_out_time)
                    <span class="text-[9px] font-bold text-indigo-600 flex items-center mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5 animate-pulse"></span>
                        Tercatat
                    </span>
                @endif
            </div>
        </div>

        <!-- STATUS BADGE (Right / Mobile Bottom) -->
        <div class="w-full lg:w-auto flex justify-start lg:justify-end mt-4 lg:mt-0 pt-4 lg:pt-0 border-t border-gray-100 lg:border-0">
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
                <div class="flex flex-col items-start lg:items-end">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Status</span>
                    <div class="inline-flex items-center rounded-xl px-4 py-2 text-xs font-black uppercase tracking-wider ring-1 ring-inset {{ $statusClass }}">
                        {{ $attendance->status_label }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Progress Indicator Bottom -->
    @php
        $progress = 0;
        if ($attendance) {
            if ($attendance->check_in_time) $progress += 50;
            if ($attendance->check_out_time) $progress += 50;
        }
    @endphp
    @if($progress > 0)
        <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-500 transition-all duration-1000 ease-out" 
             style="width: {{ $progress }}%"></div>
    @endif
</div>
