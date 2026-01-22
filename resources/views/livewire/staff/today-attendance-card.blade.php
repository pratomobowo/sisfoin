<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-6 sm:p-8 lg:p-10">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 md:gap-12">
            
            <!-- Left: Current Time & Shift Info -->
            <div class="flex items-center space-x-6 lg:min-w-[280px]">
                <div class="flex-shrink-0 w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shadow-sm border border-blue-100/50">
                    <x-lucide-clock class="w-8 h-8" />
                </div>
                <div>
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1.5">Waktu Server</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-3xl font-black text-gray-900 leading-none">{{ now()->format('H:i') }}</span>
                        <div class="px-3 py-1 bg-blue-600 text-white text-[9px] font-black rounded-lg uppercase tracking-widest shadow-sm shadow-blue-200">
                            {{ $shift->name ?? 'Normal' }}
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-tighter">
                        Shift: {{ substr($shift->start_time ?? '08:00', 0, 5) }} - {{ substr($shift->end_time ?? '16:30', 0, 5) }}
                    </p>
                </div>
            </div>

            <!-- Middle: Attendance Taps -->
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-6 md:gap-12">
                <!-- Check In -->
                <div class="relative group">
                    <div class="flex items-center space-x-5">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300
                            {{ $attendance && $attendance->check_in_time ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-gray-50 text-gray-300 border border-gray-100' }}">
                            <x-lucide-log-in class="w-7 h-7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Masuk</p>
                            <p class="text-2xl font-black {{ $attendance && $attendance->check_in_time ? 'text-gray-900' : 'text-gray-200' }}">
                                {{ $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '--:--' }}
                            </p>
                            @if($attendance && $attendance->check_in_time)
                                <span class="flex items-center text-[9px] font-black text-emerald-500 uppercase mt-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                    Tercatat
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Check Out -->
                <div class="relative group">
                    <div class="flex items-center space-x-5">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300
                            {{ $attendance && $attendance->check_out_time ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-gray-50 text-gray-300 border border-gray-100' }}">
                            <x-lucide-log-out class="w-7 h-7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pulang</p>
                            <p class="text-2xl font-black {{ $attendance && $attendance->check_out_time ? 'text-gray-900' : 'text-gray-200' }}">
                                {{ $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '--:--' }}
                            </p>
                            @if($attendance && $attendance->check_out_time)
                                <span class="flex items-center text-[9px] font-black text-blue-500 uppercase mt-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5 animate-pulse"></span>
                                    Tercatat
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Status -->
            <div class="lg:w-[220px] flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-center gap-4 border-t lg:border-t-0 lg:border-l border-gray-100 pt-6 lg:pt-0">
                <div class="text-left lg:text-right">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Status Hari Ini</p>
                    @if($attendance && $attendance->status)
                        @php
                            $statusColors = [
                                'on_time' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                                'early_arrival' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                                'late' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
                                'incomplete' => 'bg-purple-50 text-purple-700 ring-purple-600/10',
                                'absent' => 'bg-rose-50 text-rose-700 ring-rose-600/10',
                            ];
                            $statusClass = $statusColors[$attendance->status] ?? 'bg-gray-50 text-gray-700 ring-gray-600/10';
                        @endphp
                        <div class="inline-flex items-center rounded-2xl px-4 py-2 text-xs font-black uppercase tracking-wider ring-1 ring-inset {{ $statusClass }} shadow-sm">
                            {{ $attendance->status_label }}
                        </div>
                    @else
                        <span class="text-xs font-bold text-gray-300 italic tracking-tight">Belum ada catatan</span>
                    @endif
                </div>
            </div>

        </div>
    </div>
    
    <!-- Progress Bar (Subtle Visual) -->
    <div class="h-1.5 w-full bg-gray-50 overflow-hidden">
        @php
            $progress = 0;
            if ($attendance) {
                if ($attendance->check_in_time) $progress += 50;
                if ($attendance->check_out_time) $progress += 50;
            }
        @endphp
        <div class="h-full bg-gradient-to-r from-blue-500 to-emerald-500 transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(59,130,246,0.5)]" 
             style="width: {{ $progress }}%"></div>
    </div>
</div>
