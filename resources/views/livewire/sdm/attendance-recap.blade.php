<div class="space-y-6">
    <!-- Header -->
    <x-page-header 
        title="Rekap Absensi Bulanan" 
        subtitle="Laporan kehadiran seluruh karyawan per bulan"
        :breadcrumbs="['Biro SDM' => '#', 'Rekap Absensi' => route('sdm.absensi.recap')]"
    />

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Filter & Export</h2>
            <button 
                wire:click="export" 
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-wait">
                <span wire:loading.remove wire:target="export" class="flex items-center">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 22v-7.82a2 2 0 0 0-1.07-1.78l-.34-.17c-1.3-.65-2.2-1.99-2.2-3.48a4 4 0 0 1 8 0c0 1.5-.9 2.83-2.2 3.48l-.34.17A2 2 0 0 0 12 14.18V22l-3-3"/><path d="M15 22l3-3"/></svg>
                    Export Excel
                </span>
                <span wire:loading wire:target="export">
                    Generating...
                </span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Karyawan</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Nama atau NIP..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Toggle Custom Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mode Filter</label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="useCustomRange" class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900">{{ $useCustomRange ? 'Custom' : 'Bulanan' }}</span>
                </label>
            </div>

            @if($useCustomRange)
                <!-- Custom Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input 
                        type="date" 
                        wire:model.live="dateFrom"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Custom Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input 
                        type="date" 
                        wire:model.live="dateTo"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            @else
                <!-- Month Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                    <select 
                        wire:model.live="month"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <select 
                        wire:model.live="year"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Unit Kerja Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                <select 
                    wire:model.live="unitKerja"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($this->unitKerjaList as $unit)
                        <option value="{{ $unit }}">{{ $unit }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Matrix Table -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden flex flex-col" style="max-height: calc(100-250px);">
        <!-- Legend -->
        <div class="p-4 bg-gray-50/50 border-b border-gray-100 flex flex-wrap gap-4 text-[10px] uppercase font-bold tracking-wider text-gray-500">
            <div class="flex items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span> Hadir</div>
            <div class="flex items-center"><span class="w-2 h-2 rounded-full bg-yellow-400 mr-2"></span> Terlambat</div>
            <div class="flex items-center"><span class="w-2 h-2 rounded-full bg-purple-600 mr-2"></span> Data Tidak Lengkap</div>
            <div class="flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span> Tidak Hadir</div>
            <div class="flex items-center"><span class="w-2 h-2 rounded-full bg-blue-400 mr-2"></span> Setengah Hari</div>
            <div class="flex items-center"><span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span> Sakit</div>
            <div class="flex items-center"><span class="w-2 h-2 rounded-full bg-pink-400 mr-2"></span> Cuti</div>
        </div>

        <div class="overflow-auto flex-grow h-[600px]">
            <table class="min-w-full border-separate border-spacing-0">
                <thead class="bg-gray-50 sticky top-0 z-30">
                    <tr>
                        <th scope="col" class="sticky left-0 top-0 z-40 bg-gray-50 px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest border-b border-r border-gray-200 w-72 min-w-[280px]">
                            Karyawan
                        </th>
                        @foreach($days as $day)
                            <th scope="col" class="sticky top-0 z-20 px-2 py-3 text-center text-[10px] font-bold uppercase tracking-tighter border-b border-r border-gray-100 min-w-[42px] 
                                {{ $day['is_holiday'] ? 'bg-red-100 text-red-600' : ($day['is_weekend'] ? 'bg-red-50/70 text-red-400' : 'text-gray-400') }}">
                                <div class="flex flex-col">
                                    <span class="opacity-70">{{ $day['weekday'] }}</span>
                                    <span class="text-xs {{ $day['is_holiday'] ? 'text-red-700' : 'text-gray-700' }}">{{ $day['day'] }}</span>
                                    @if($day['is_holiday'])
                                        <span class="text-[8px] text-red-500" title="{{ $day['holiday_name'] }}">L</span>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <!-- Sticky Employee Name -->
                            <td class="sticky left-0 z-20 bg-white group-hover:bg-blue-50 px-6 py-4 whitespace-nowrap border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-9 w-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                        {{ strtoupper(substr(($employee->name ?? 'U'), 0, 2)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900 truncate max-w-[180px]" title="{{ $employee->full_name_with_title ?? $employee->name }}">
                                            {{ $employee->full_name_with_title ?? $employee->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-[10px] font-medium text-gray-400 uppercase tracking-widest">{{ $employee->nip ?? 'TANPA NIP' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Daily Data -->
                            @foreach($days as $day)
                                @php
                                    $data = $attendanceMatrix[$employee->id][$day['day']] ?? null;
                                    $cellStyle = $day['is_weekend'] ? 'bg-red-50/30' : '';
                                    
                                    if ($data) {
                                        $cellStyle = match($data['status']) {
                                            'present', 'on_time', 'early_arrival' => 'bg-green-500 text-white font-bold',
                                            'late' => 'bg-yellow-400 text-white font-bold',
                                            'absent' => 'bg-red-500 text-white font-bold',
                                            'incomplete' => 'bg-purple-600 text-white font-bold',
                                            'half_day' => 'bg-blue-400 text-white font-bold',
                                            'sick' => 'bg-gray-400 text-white font-bold',
                                            'leave' => 'bg-pink-400 text-white font-bold',
                                            default => ''
                                        };
                                    }
                                @endphp
                                <td class="p-1 text-center whitespace-nowrap border-r border-b border-gray-50 {{ $day['is_weekend'] && !$data ? 'bg-red-50/30' : '' }}">
                                    @if($data)
                                        <div class="group/cell relative flex items-center justify-center">
                                            <div class="w-8 h-8 rounded-lg {{ $cellStyle }} flex items-center justify-center text-xs shadow-sm transition-transform hover:scale-110">
                                                {{ $data['short_label'] }}
                                            </div>
                                            
                                            <!-- Tooltip (Improved) -->
                                            <div class="opacity-0 group-hover/cell:opacity-100 pointer-events-none absolute bottom-full left-1/2 transform -translate-x-1/2 mb-3 w-56 bg-gray-900/95 backdrop-blur-sm text-white text-[10px] rounded-xl p-3 z-50 shadow-2xl transition-all duration-200 flex flex-col gap-1">
                                                <div class="flex justify-between items-center border-b border-gray-700 pb-2 mb-2">
                                                    <span class="font-bold text-gray-400">{{ \Carbon\Carbon::parse($day['date'])->isoFormat('dddd, D MMM') }}</span>
                                                    <span class="px-2 py-0.5 rounded-full bg-gray-700 text-[9px] uppercase">{{ $data['status'] }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Jam Masuk</span>
                                                    <span class="font-bold text-green-400">{{ $data['check_in'] ?? '--:--' }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Jam Pulang</span>
                                                    <span class="font-bold text-blue-400">{{ $data['check_out'] ?? '--:--' }}</span>
                                                </div>
                                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-200 text-[10px]">.</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($days) + 1 }}" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-gray-400 font-medium italic">Tidak ada data kehadiran yang ditemukan untuk periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</div>
</div>
