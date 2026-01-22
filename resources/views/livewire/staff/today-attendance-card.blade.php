<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-5 border-b border-gray-50 flex justify-between items-center">
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center">
            <x-lucide-clock class="w-4 h-4 mr-2 text-blue-600" />
            Kehadiran Hari Ini
        </h3>
        <span class="text-[10px] font-bold py-1 px-2 bg-blue-50 text-blue-700 rounded-full uppercase tracking-tighter">
            {{ now()->locale('id')->isoFormat('D MMMM YYYY') }}
        </span>
    </div>
    
    <div class="p-6">
        @if($shift)
            <div class="mb-6 p-3 bg-gray-50 rounded-xl border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Shift Anda:</span>
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">{{ $shift->name }}</span>
                </div>
                <div class="flex items-center space-x-2 text-sm font-black text-gray-700">
                    <x-lucide-calendar-range class="w-4 h-4 text-gray-400" />
                    <span>{{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <!-- Check In -->
            <div class="space-y-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Jam Masuk</span>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $attendance && $attendance->check_in_time ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-300' }}">
                        <x-lucide-log-in class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-lg font-black {{ $attendance && $attendance->check_in_time ? 'text-gray-900' : 'text-gray-300' }}">
                            {{ $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '--:--' }}
                        </p>
                        @if($attendance && $attendance->check_in_time)
                            <span class="text-[9px] font-bold text-emerald-500 uppercase">Tercatat</span>
                        @else
                            <span class="text-[9px] font-bold text-gray-400 uppercase">Belum Absen</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Check Out -->
            <div class="space-y-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Jam Keluar</span>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $attendance && $attendance->check_out_time ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-300' }}">
                        <x-lucide-log-out class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-lg font-black {{ $attendance && $attendance->check_out_time ? 'text-gray-900' : 'text-gray-300' }}">
                            {{ $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '--:--' }}
                        </p>
                        @if($attendance && $attendance->check_out_time)
                            <span class="text-[9px] font-bold text-blue-500 uppercase">Tercatat</span>
                        @else
                            <span class="text-[9px] font-bold text-gray-400 uppercase">Belum Absen</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($attendance && $attendance->status)
            <div class="mt-6 pt-5 border-t border-gray-50 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Status Saat Ini:</span>
                @php
                    $statusColors = [
                        'on_time' => 'bg-emerald-100 text-emerald-700',
                        'early_arrival' => 'bg-emerald-100 text-emerald-700',
                        'late' => 'bg-amber-100 text-amber-700',
                        'incomplete' => 'bg-purple-100 text-purple-700',
                        'absent' => 'bg-rose-100 text-rose-700',
                    ];
                    $statusClass = $statusColors[$attendance->status] ?? 'bg-gray-100 text-gray-700';
                @endphp
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $statusClass }}">
                    {{ $attendance->status_label }}
                </span>
            </div>
        @endif
    </div>
</div>
