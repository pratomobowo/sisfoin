<div class="space-y-6">
    {{-- Header --}}
    <x-page-header 
        :title="$unitName . ' - Kalendar Shift'" 
        subtitle="Kelola jadwal shift dengan calendar view"
        :breadcrumbs="[
            'Biro SDM' => '#', 
            'Kelola Shift Unit' => route('sdm.absensi.kelola-shift'),
            $unitName => route('sdm.absensi.unit-detail', ['unit' => $unitSlug])
        ]"
    >
        <x-slot name="actions">
            <div class="flex gap-2">
                <x-button variant="secondary" onclick="window.location='{{ route('sdm.absensi.unit-list', ['unit' => $unitSlug]) }}'">
                    <x-slot name="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </x-slot>
                    List View
                </x-button>
                
                <x-button variant="primary" onclick="window.location='{{ route('sdm.absensi.unit-detail', ['unit' => $unitSlug]) }}'">
                    <x-slot name="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </x-slot>
                    Tambah Assignment
                </x-button>
            </div>
        </x-slot>
    </x-page-header>

    @if($this->employees->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Belum Ada Pegawai</h3>
            <p class="text-gray-500 mt-1">Tidak ada pegawai aktif di unit ini yang memiliki akun user.</p>
        </div>
    @else
        {{-- Month Navigation --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center justify-between">
            <button wire:click="previousMonth" class="p-2 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <h2 class="text-2xl font-bold text-gray-900">{{ $currentMonthName }}</h2>
            
            <button wire:click="nextMonth" class="p-2 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        {{-- Calendar Grid --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-max">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="sticky left-0 bg-gray-50 px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200 min-w-[200px] z-10">
                                Pegawai
                            </th>
                            @foreach($this->calendarDates as $date)
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase min-w-[80px] border-r border-gray-100">
                                    <div>{{ $date->locale('id')->isoFormat('ddd') }}</div>
                                    <div class="text-lg font-bold text-gray-900 mt-1">{{ $date->day }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($this->employees as $employee)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="sticky left-0 bg-white hover:bg-gray-50 px-4 py-3 border-r border-gray-200 z-10">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-900 truncate">{{ $employee->name }}</p>
                                            <p class="text-xs text-gray-500 font-mono">{{ $employee->nip }}</p>
                                        </div>
                                    </div>
                                </td>
                                @foreach($this->calendarDates as $date)
                                    @php
                                        $shift = $this->getEmployeeShiftForDate($employee->id, $date);
                                        $cellKey = "{$employee->id}_{$date->toDateString()}";
                                        $isSelected = $selectedCell === $cellKey;
                                        $isWeekend = $date->isWeekend();
                                    @endphp
                                    <td class="px-3 py-3 text-center border-r border-gray-100 {{ $isWeekend ? 'bg-gray-50/50' : '' }}" wire:key="cell-{{ $cellKey }}">
                                        <button 
                                            wire:click="openQuickEdit({{ $employee->id }}, '{{ $date->toDateString() }}')"
                                            class="w-full px-2 py-2 rounded-lg transition-all {{ $shift ? 'bg-' . $shift->color . '-100 hover:bg-' . $shift->color . '-200 border border-' . $shift->color . '-300' : 'bg-gray-100 hover:bg-gray-200 border border-gray-200' }} {{ $isSelected ? 'ring-2 ring-blue-500 scale-105' : '' }}"
                                        >
                                            @if($shift)
                                                <div class="text-xs font-bold text-{{ $shift->color }}-800">{{ $shift->code ?? substr($shift->name, 0, 1) }}</div>
                                                <div class="text-[10px] text-{{ $shift->color }}-600 mt-0.5">{{ substr($shift->start_time, 0, 5) }}</div>
                                            @else
                                                <div class="text-xs font-medium text-gray-500">-</div>
                                            @endif
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Legend --}}
        <div class="bg-gray-50/50 rounded-xl border border-gray-100 p-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Legenda Shift
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($this->shifts as $shift)
                    <div class="flex items-center p-3 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-lg bg-{{ $shift->color }}-100 border border-{{ $shift->color }}-200 flex items-center justify-center flex-shrink-0 mr-3">
                            <span class="text-[10px] font-black text-{{ $shift->color }}-800 leading-none uppercase">{{ $shift->code ?? substr($shift->name, 0, 1) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $shift->name }}</p>
                            <p class="text-[11px] text-gray-500 font-medium">
                                {{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }}
                            </p>
                        </div>
                    </div>
                @endforeach
                
                {{-- Default Legend --}}
                <div class="flex items-center p-3 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center flex-shrink-0 mr-3">
                        <span class="text-xs font-black text-gray-400 leading-none">-</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">Default</p>
                        <p class="text-[11px] text-gray-500 font-medium italic">Jam kerja global</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Quick Edit Modal --}}
    @if($selectedCell)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeQuickEdit"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
                    @php
                        [$userId, $dateStr] = explode('_', $selectedCell);
                        $employee = $this->employees->firstWhere('id', $userId);
                        $date = \Carbon\Carbon::parse($dateStr);
                    @endphp

                    @if(!$employee)
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            User tidak ditemukan pada unit ini. Silakan tutup panel dan pilih sel yang valid.
                        </div>
                    @else
                    
                    <div class="flex items-center gap-3 mb-6 p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 leading-tight truncate">{{ $employee->name }}</p>
                            <p class="text-[10px] text-gray-500 font-mono uppercase tracking-wider">{{ $employee->nip }}</p>
                        </div>
                    </div>

                    
                    <div class="space-y-4 mb-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Dari Tanggal</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-900">
                                    {{ $date->locale('id')->isoFormat('D MMM YYYY') }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hingga Tanggal</label>
                                <input type="date" wire:model="quickEditEndDate" 
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-semibold focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <button wire:click="selectWeek" type="button" class="w-full py-2 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Pilih 1 Minggu (7 Hari)
                        </button>
                    </div>
                    
                    <div class="space-y-2 mb-6">
                        <label class="block">
                            <input type="radio" wire:model="quickEditShiftId" value="" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Default (Tidak ada shift khusus)</span>
                        </label>
                        @foreach($this->shifts as $shift)
                            <label class="block p-3 rounded-lg border-2 hover:bg-{{ $shift->color }}-50 cursor-pointer transition-colors {{ $quickEditShiftId == $shift->id ? 'border-' . $shift->color . '-500 bg-' . $shift->color . '-50' : 'border-gray-200' }}">
                                <input type="radio" wire:model="quickEditShiftId" value="{{ $shift->id }}" class="mr-2">
                                <span class="font-semibold text-gray-900">{{ $shift->name }}</span>
                                <span class="text-sm text-gray-500 ml-2">({{ substr($shift->start_time, 0, 5) }}-{{ substr($shift->end_time, 0, 5) }})</span>
                            </label>
                        @endforeach
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button wire:click="closeQuickEdit"
                                class="px-5 py-2.5 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button wire:click="saveQuickEdit"
                                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl">
                            Simpan
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
