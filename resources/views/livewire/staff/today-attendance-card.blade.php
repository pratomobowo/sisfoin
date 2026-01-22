<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-8 sm:p-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-0 items-center">
            
            <!-- Section 1: Server Time & Shift (Left) -->
            <div class="lg:col-span-4 flex items-center space-x-6 lg:border-r border-gray-100 lg:pr-10">
                <div class="flex-shrink-0 w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shadow-inner border border-blue-100/50">
                    <x-lucide-clock class="w-8 h-8" />
                </div>
                <div class="space-y-1">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Waktu Server</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-4xl font-black text-gray-900 leading-none">{{ now()->format('H:i') }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="px-2.5 py-1 bg-blue-600 text-white text-[9px] font-black rounded-lg uppercase tracking-widest shadow-sm">
                            {{ $shift->name ?? 'Normal' }}
                        </span>
                        <span class="text-[10px] font-bold text-gray-500 py-1 uppercase tracking-tighter">
                            {{ substr($shift->start_time ?? '08:00', 0, 5) }} - {{ substr($shift->end_time ?? '16:30', 0, 5) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Check-In (Middle Left) -->
            <div class="lg:col-span-3 lg:border-r border-gray-100 lg:px-10">
                <div class="flex items-center space-x-5">
                    <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-sm
                        {{ $attendance && $attendance->check_in_time ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-gray-50 text-gray-300 border border-gray-100' }}">
                        <x-lucide-log-in class="w-7 h-7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Masuk</p>
                        <p class="text-3xl font-black {{ $attendance && $attendance->check_in_time ? 'text-gray-900' : 'text-gray-200' }}">
                            {{ $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '--:--' }}
                        </p>
                        @if($attendance && $attendance->check_in_time)
                            <div class="flex items-center text-[9px] font-black text-emerald-500 uppercase mt-1 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100/50 w-fit">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                Tercatat
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Section 3: Check-Out (Middle Right) -->
            <div class="lg:col-span-3 lg:border-r border-gray-100 lg:px-10">
                <div class="flex items-center space-x-5">
                    <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-sm
                        {{ $attendance && $attendance->check_out_time ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-gray-50 text-gray-300 border border-gray-100' }}">
                        <x-lucide-log-out class="w-7 h-7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Pulang</p>
                        <p class="text-3xl font-black {{ $attendance && $attendance->check_out_time ? 'text-gray-900' : 'text-gray-200' }}">
                            {{ $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '--:--' }}
                        </p>
                        @if($attendance && $attendance->check_out_time)
                            <div class="flex items-center text-[9px] font-black text-indigo-500 uppercase mt-1 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100/50 w-fit">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5 animate-pulse"></span>
                                Tercatat
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Section 4: Status (Right) -->
            <div class="lg:col-span-2 lg:pl-10 text-center lg:text-right">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status Anda</p>
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
                    <div class="inline-flex items-center rounded-2xl px-5 py-2.5 text-[11px] font-black uppercase tracking-wider ring-1 ring-inset {{ $statusClass }} shadow-sm">
                        {{ $attendance->status_label }}
                    </div>
                @else
                    <span class="text-xs font-bold text-gray-300 italic">Belum ada data</span>
                @endif
            </div>

        </div>
    </div>
    
    <!-- Progress Indicator -->
    <div class="h-2 w-full bg-gray-50 overflow-hidden border-t border-gray-100">
        @php
            $progress = 0;
            if ($attendance) {
                if ($attendance->check_in_time) $progress += 50;
                if ($attendance->check_out_time) $progress += 50;
            }
        @endphp
        <div class="h-full bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-500 transition-all duration-1000 ease-out" 
             style="width: {{ $progress }}%"></div>
    </div>
</div>
