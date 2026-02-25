<div class="space-y-6">
    <x-page-header
        title="Monitor Absensi SDM"
        subtitle="Pantau kehadiran harian dan ringkasan rentang tanggal"
        :breadcrumbs="['Biro SDM' => '#', 'Monitor Absensi' => route('sdm.absensi.monitor')]"
    >
        <x-slot name="actions">
            <x-button
                variant="warning"
                wire:click="reprocessAllAttendance"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="reprocessAllAttendance">Proses Ulang Data Absensi</span>
                <span wire:loading wire:target="reprocessAllAttendance">Memproses...</span>
            </x-button>
        </x-slot>
    </x-page-header>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <div>
            <h2 class="text-base font-semibold text-gray-900">Filter Monitor</h2>
            <p class="text-xs text-gray-500">Atur mode dan rentang data untuk menampilkan monitor kehadiran.</p>
        </div>

        @if (session()->has('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if($lastAttendanceOperation)
            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-800">
                <span class="font-semibold">Proses terakhir:</span>
                {{ $lastAttendanceOperation->created_at?->format('d/m/Y H:i:s') }}
                oleh {{ $lastAttendanceOperation->causer?->name ?? 'System' }}
                | aksi: {{ $lastAttendanceOperation->properties['action'] ?? '-' }}
                | processed: {{ $lastAttendanceOperation->properties['processed_count'] ?? 0 }}
                | errors: {{ $lastAttendanceOperation->properties['error_count'] ?? 0 }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                <select wire:model.live="mode" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="daily">Harian</option>
                    <option value="range">Rentang Tanggal</option>
                </select>
            </div>

            @if($mode === 'daily')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" wire:model.live="selectedDate" class="w-full border border-gray-300 rounded-lg px-3 py-2" />
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Awal</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full border border-gray-300 rounded-lg px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                    <input type="date" wire:model.live="dateTo" class="w-full border border-gray-300 rounded-lg px-3 py-2" />
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                <select wire:model.live="unitKerja" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($this->unitKerjaList as $unit)
                        <option value="{{ $unit }}">{{ $unit }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama/NIP</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama atau NIP..." class="w-full border border-gray-300 rounded-lg px-3 py-2" />
            </div>

            @if(!$isRange)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model.live="statusFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Semua</option>
                        <option value="on_time">Hadir</option>
                        <option value="early_arrival">Datang Lebih Awal</option>
                        <option value="late">Terlambat</option>
                        <option value="incomplete">Tidak Lengkap</option>
                        <option value="sick">Sakit</option>
                        <option value="leave">Cuti</option>
                        <option value="permission">Izin</option>
                        <option value="absent">Tidak Hadir</option>
                        <option value="holiday">Libur</option>
                    </select>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            @if($isRange)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Karyawan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit Kerja</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Hari Kerja</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Hadir</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Terlambat</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tidak Hadir</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Sakit</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Cuti</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Izin</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">% Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($rows as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <div class="font-medium">{{ $row['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $row['nip'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $row['unit_kerja'] }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-700">{{ $row['working_days'] }}</td>
                                <td class="px-4 py-3 text-center text-sm text-green-700 font-semibold">
                                    <button type="button" wire:click="toggleRangeDetails({{ $row['employee_id'] }})" class="hover:underline decoration-dotted">
                                        {{ $row['hadir'] }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-yellow-700 font-semibold">
                                    <button type="button" wire:click="toggleRangeDetails({{ $row['employee_id'] }})" class="hover:underline decoration-dotted">
                                        {{ $row['late'] }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-red-700 font-semibold">
                                    <button type="button" wire:click="toggleRangeDetails({{ $row['employee_id'] }})" class="hover:underline decoration-dotted">
                                        {{ $row['absent'] }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-700">
                                    <button type="button" wire:click="toggleRangeDetails({{ $row['employee_id'] }})" class="hover:underline decoration-dotted">
                                        {{ $row['sick'] }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-indigo-700">
                                    <button type="button" wire:click="toggleRangeDetails({{ $row['employee_id'] }})" class="hover:underline decoration-dotted">
                                        {{ $row['leave'] }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-blue-700">
                                    <button type="button" wire:click="toggleRangeDetails({{ $row['employee_id'] }})" class="hover:underline decoration-dotted">
                                        {{ $row['permission'] ?? 0 }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600">
                                    <button type="button" wire:click="toggleRangeDetails({{ $row['employee_id'] }})" class="inline-flex items-center px-2 py-1 rounded-md border border-gray-300 hover:bg-gray-50 text-xs font-medium">
                                        {{ $expandedRangeEmployeeId === $row['employee_id'] ? 'Tutup' : 'Lihat Hari' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center text-sm font-semibold">{{ number_format($row['attendance_rate'], 2) }}%</td>
                            </tr>

                            @if($expandedRangeEmployeeId === $row['employee_id'])
                                <tr class="bg-gray-50/70">
                                    <td colspan="10" class="px-4 py-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                                            @php
                                                $detailGroups = [
                                                    ['key' => 'hadir', 'label' => 'Hadir', 'chip' => 'bg-green-50 text-green-700 border-green-200'],
                                                    ['key' => 'terlambat', 'label' => 'Terlambat', 'chip' => 'bg-yellow-50 text-yellow-700 border-yellow-200'],
                                                    ['key' => 'tidak_hadir', 'label' => 'Tidak Hadir', 'chip' => 'bg-red-50 text-red-700 border-red-200'],
                                                    ['key' => 'sakit', 'label' => 'Sakit', 'chip' => 'bg-gray-100 text-gray-700 border-gray-200'],
                                                    ['key' => 'cuti', 'label' => 'Cuti', 'chip' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                                                    ['key' => 'izin', 'label' => 'Izin', 'chip' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                                    ['key' => 'tidak_lengkap', 'label' => 'Tidak Lengkap', 'chip' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                                    ['key' => 'setengah_hari', 'label' => 'Setengah Hari', 'chip' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                                ];
                                            @endphp

                                            @foreach($detailGroups as $group)
                                                <div class="rounded-lg border border-gray-200 bg-white p-3">
                                                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $group['label'] }}</div>
                                                    @if(!empty($row['status_dates'][$group['key']]))
                                                        <div class="flex flex-wrap gap-1.5">
                                                            @foreach($row['status_dates'][$group['key']] as $date)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-xs {{ $group['chip'] }}">
                                                                    {{ \Carbon\Carbon::parse($date)->format('d/m') }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="text-xs text-gray-400">-</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data monitor pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Karyawan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit Kerja</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pulang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($rows as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <div class="font-medium">{{ $row['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $row['nip'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $row['unit_kerja'] }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $badge = match($row['status']) {
                                            'on_time', 'present', 'early_arrival' => 'bg-green-100 text-green-800',
                                            'late' => 'bg-yellow-100 text-yellow-800',
                                            'absent' => 'bg-red-100 text-red-800',
                                            'holiday' => 'bg-slate-100 text-slate-700',
                                            'sick' => 'bg-gray-100 text-gray-700',
                                            'leave' => 'bg-indigo-100 text-indigo-700',
                                            'permission' => 'bg-blue-100 text-blue-700',
                                            'incomplete' => 'bg-purple-100 text-purple-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $row['check_in'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $row['check_out'] }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $row['source'] === 'record' ? 'Data Absensi' : 'Inferensi Monitor' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data monitor untuk tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        @if($rows->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-white">
                <x-superadmin.pagination
                    :currentPage="$rows->currentPage()"
                    :lastPage="$rows->lastPage()"
                    :total="$rows->total()"
                    :perPage="$rows->perPage()"
                    :perPageOptions="[15, 25, 50, 100]"
                    :showPageInfo="true"
                    :showPerPage="true"
                    alignment="justify-between"
                    perPageWireModel="perPage"
                    previousPageWireModel="previousPage"
                    nextPageWireModel="nextPage"
                    gotoPageWireModel="gotoPage"
                />
            </div>
        @endif
    </div>
</div>
