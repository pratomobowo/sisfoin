<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="p-8 sm:p-10">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <!-- Left: Current Time & Shift -->
            <div class="flex items-center space-x-6 pr-8 lg:border-r border-gray-100">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shadow-inner">
                    <x-lucide-clock class="w-8 h-8" />
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em] mb-1">Kehadiran Hari Ini</h3>
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl font-black text-gray-900 tracking-tight">{{ now()->format('H:i') }}</span>
                        <div class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg border border-blue-100 uppercase tracking-wider">
                            {{ $shift->name }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle: Check In & Check Out -->
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-8 items-center">
                <!-- Check In -->
                <div class="flex items-center space-x-5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center 
                        {{ $attendance && $attendance->check_in_time ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-50 text-gray-300' }} shadow-inner">
                        <x-lucide-log-in class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Jam Masuk</p>
                        <p class="text-xl font-black {{ $attendance && $attendance->check_in_time ? 'text-gray-900' : 'text-gray-200' }}">
                            {{ $attendance && $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '--:--' }}
                        </p>
                    </div>
                </div>

                <!-- Check Out -->
                <div class="flex items-center space-x-5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center 
                        {{ $attendance && $attendance->check_out_time ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-300' }} shadow-inner">
                        <x-lucide-log-out class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Jam Keluar</p>
                        <p class="text-xl font-black {{ $attendance && $attendance->check_out_time ? 'text-gray-900' : 'text-gray-200' }}">
                            {{ $attendance && $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '--:--' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right: Status Badge -->
            <div class="lg:pl-8 lg:border-l border-gray-100 flex flex-col items-center lg:items-end justify-center">
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
                    <span class="px-5 py-2 rounded-2xl text-xs font-black uppercase tracking-widest ring-1 ring-inset {{ $statusClass }} shadow-sm">
                        {{ $attendance->status_label }}
                    </span>
                    <p class="text-[9px] font-bold text-gray-400 uppercase mt-2">Status Kehadiran</p>
                @else
                    <div class="text-center lg:text-right">
                        <span class="text-xs font-bold text-gray-300 italic">Belum ada data absensi hari ini</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
