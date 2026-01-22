<div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-12 lg:p-16 mb-10 relative overflow-hidden transition-all duration-500 hover:shadow-xl hover:shadow-blue-500/5">
    <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-12 lg:gap-24">
        
        <!-- BLOCK 1: WAKTU & SHIFT (Left) -->
        <div class="flex items-center gap-10 lg:min-w-[320px]">
            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-3xl flex items-center justify-center shadow-inner border border-blue-100/50 flex-shrink-0">
                <x-lucide-clock class="w-10 h-10" />
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.25em] mb-3">Waktu Server</span>
                <span class="text-5xl font-black text-gray-900 leading-none tracking-tight">{{ now()->format('H:i') }}</span>
                <div class="flex items-center gap-3 mt-4">
                    <span class="px-3 py-1.5 bg-blue-600 text-white text-[9px] font-black rounded-xl uppercase tracking-widest shadow-sm">
                        {{ $shift->name ?? 'Normal' }}
                    </span>
                    <span class="text-[10px] font-bold text-gray-400 tracking-tight">
                        {{ substr($shift->start_time ?? '08:00', 0, 5) }} - {{ substr($shift->end_time ?? '16:30', 0, 5) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- BLOCK 2: ABSEN MASUK (Middle) -->
        <div class="flex flex-col pl-10 border-l-4 {{ $attendance && $attendance->check_in_time ? 'border-emerald-500' : 'border-gray-100' }} transition-all duration-500">
            <span class="text-[10px] font-black {{ $attendance && $attendance->check_in_time ? 'text-gray-400' : 'text-gray-300' }} uppercase tracking-[0.25em] mb-3">Jam Masuk</span>
            <span class="text-5xl font-black {{ $attendance && $attendance->check_in_time ? 'text-gray-900' : 'text-gray-100' }} tracking-tight">
                {{ $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '--:--' }}
            </span>
            @if($attendance && $attendance->check_in_time)
                <div class="flex items-center mt-3.5 text-emerald-600">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 mr-2.5 animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest">Tercatat</span>
                </div>
            @else
                <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest mt-3">Belum Absen</span>
            @endif
        </div>

        <!-- BLOCK 3: ABSEN PULANG (Middle) -->
        <div class="flex flex-col pl-10 border-l-4 {{ $attendance && $attendance->check_out_time ? 'border-indigo-500' : 'border-gray-100' }} transition-all duration-500">
            <span class="text-[10px] font-black {{ $attendance && $attendance->check_out_time ? 'text-gray-400' : 'text-gray-300' }} uppercase tracking-[0.25em] mb-3">Jam Pulang</span>
            <span class="text-5xl font-black {{ $attendance && $attendance->check_out_time ? 'text-gray-900' : 'text-gray-100' }} tracking-tight">
                {{ $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '--:--' }}
            </span>
            @if($attendance && $attendance->check_out_time)
                <div class="flex items-center mt-3.5 text-indigo-600">
                    <div class="w-2 h-2 rounded-full bg-indigo-500 mr-2.5 animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest">Tercatat</span>
                </div>
            @else
                <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest mt-3">Belum Absen</span>
            @endif
        </div>

        <!-- BLOCK 4: STATUS BADGE (Right) -->
        <div class="flex flex-col items-start lg:items-end justify-center lg:ml-auto pt-10 lg:pt-0 border-t lg:border-t-0 border-gray-50">
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
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.25em] mb-4">Status Kehadiran</span>
                <div class="inline-flex items-center rounded-2xl px-6 py-3 text-xs font-black uppercase tracking-widest ring-1 ring-inset {{ $statusClass }} shadow-sm">
                    {{ $attendance->status_label }}
                </div>
            @else
                <div class="flex flex-col items-start lg:items-end opacity-30">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.25em] mb-3">Status</span>
                    <span class="text-xs font-black text-gray-400 italic">No Data</span>
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
    <div class="absolute bottom-0 left-0 w-full h-2 bg-gray-50/50">
        <div class="h-full bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-600 transition-all duration-1000 ease-in-out" 
             style="width: {{ $progress }}%"></div>
    </div>
</div>
