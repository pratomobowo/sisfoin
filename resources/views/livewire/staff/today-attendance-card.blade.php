<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 relative overflow-hidden">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
        
        <!-- BLOCK 1: WAKTU & SHIFT -->
        <div class="flex items-center gap-4 min-w-[240px]">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center border border-blue-100 flex-shrink-0">
                <x-lucide-clock class="w-6 h-6" />
            </div>
            <div class="flex flex-col">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Server</span>
                <span class="text-2xl font-bold text-gray-900 leading-tight">{{ now()->format('H:i') }}</span>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2 py-0.5 bg-blue-600 text-white text-[10px] font-bold rounded uppercase tracking-tight">
                        {{ $shift->name ?? 'Normal' }}
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        {{ substr($shift->start_time ?? '08:00', 0, 5) }} - {{ substr($shift->end_time ?? '16:30', 0, 5) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- BLOCK 2: ABSEN MASUK -->
        <div class="flex flex-col pl-5 border-l-2 {{ $attendance && $attendance->check_in_time ? 'border-emerald-500' : 'border-gray-100' }}">
            <span class="text-xs font-medium {{ $attendance && $attendance->check_in_time ? 'text-gray-500' : 'text-gray-400' }} uppercase tracking-wider">Jam Masuk</span>
            <span class="text-2xl font-bold {{ $attendance && $attendance->check_in_time ? 'text-gray-900' : 'text-gray-200' }}">
                {{ $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '--:--' }}
            </span>
            @if($attendance && $attendance->check_in_time)
                <div class="flex items-center mt-1 text-emerald-600">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></div>
                    <span class="text-[10px] font-bold uppercase tracking-tight">Tercatat</span>
                </div>
            @else
                <span class="text-[10px] font-medium text-gray-300 uppercase tracking-tight mt-1">Belum Absen</span>
            @endif
        </div>

        <!-- BLOCK 3: ABSEN PULANG -->
        <div class="flex flex-col pl-5 border-l-2 {{ $attendance && $attendance->check_out_time ? 'border-indigo-500' : 'border-gray-100' }}">
            <span class="text-xs font-medium {{ $attendance && $attendance->check_out_time ? 'text-gray-500' : 'text-gray-400' }} uppercase tracking-wider">Jam Pulang</span>
            <span class="text-2xl font-bold {{ $attendance && $attendance->check_out_time ? 'text-gray-900' : 'text-gray-200' }}">
                {{ $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '--:--' }}
            </span>
            @if($attendance && $attendance->check_out_time)
                <div class="flex items-center mt-1 text-indigo-600">
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-2 animate-pulse"></div>
                    <span class="text-[10px] font-bold uppercase tracking-tight">Tercatat</span>
                </div>
            @else
                <span class="text-[10px] font-medium text-gray-300 uppercase tracking-tight mt-1">Belum Absen</span>
            @endif
        </div>

        <!-- BLOCK 4: STATUS BADGE -->
        <div class="flex flex-col items-start lg:items-end justify-center lg:ml-auto pt-6 lg:pt-0 border-t lg:border-t-0 border-gray-50">
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
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Status Kehadiran</span>
                <div class="inline-flex items-center rounded-lg px-4 py-2 text-xs font-bold uppercase tracking-wide ring-1 ring-inset {{ $statusClass }}">
                    {{ $attendance->status_label }}
                </div>
            @else
                <div class="flex flex-col items-start lg:items-end opacity-40">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Status Kehadiran</span>
                    <span class="text-xs font-bold text-gray-400 italic">Data Belum Tersedia</span>
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
