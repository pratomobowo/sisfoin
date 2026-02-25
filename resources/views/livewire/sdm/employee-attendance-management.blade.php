<div class="space-y-6">
    <!-- Header -->
    <x-page-header 
        title="Koreksi Absensi SDM" 
        subtitle="Koreksi manual absensi masuk/pulang, sakit, cuti, dan penyesuaian status"
        :breadcrumbs="['Biro SDM' => '#', 'Absensi Karyawan' => route('sdm.absensi.management')]"
    >
        <x-slot name="actions">
            <x-button
                variant="danger"
                wire:click="openClearSection"
                wire:loading.attr="disabled"
                class="mr-2"
            >
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </x-slot>
                Hapus Data
            </x-button>
            <x-button 
                variant="primary"
                wire:click="create">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </x-slot>
                <span>Tambah Absensi</span>
            </x-button>
        </x-slot>
    </x-page-header>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-2">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <button
                type="button"
                wire:click="switchTab('daily-correction')"
                class="px-4 py-3 rounded-lg text-left transition border {{ $currentTab === 'daily-correction' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50' }}"
            >
                <div class="text-sm font-semibold">Koreksi Harian SDM</div>
                <div class="text-xs text-gray-500">Tampilkan semua karyawan aktif, lalu koreksi manual yang lupa absen / sakit / cuti.</div>
            </button>
            <button
                type="button"
                wire:click="switchTab('history')"
                class="px-4 py-3 rounded-lg text-left transition border {{ $currentTab === 'history' ? 'bg-slate-50 border-slate-300 text-slate-800' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50' }}"
            >
                <div class="text-sm font-semibold">Riwayat Record Absensi</div>
                <div class="text-xs text-gray-500">Daftar record absensi yang sudah tersimpan untuk audit dan koreksi lanjutan.</div>
            </button>
        </div>
    </div>

    @if($showClearSection)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-red-800 mb-1">Konfirmasi Hapus Semua Data Absensi</label>
                <p class="text-xs text-red-700 mb-2">Untuk menjalankan tombol <strong>Hapus Data</strong>, ketik <code class="px-1 py-0.5 bg-red-100 rounded">HAPUS ABSENSI</code> di bawah ini.</p>
                <input
                    type="text"
                    wire:model.live="clearConfirmation"
                    placeholder="HAPUS ABSENSI"
                    class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:border-red-400"
                >
            </div>
            <div class="text-xs text-red-700 bg-white/70 border border-red-200 rounded-lg px-3 py-2">
                Status: {{ trim((string) $clearConfirmation) === 'HAPUS ABSENSI' ? 'Siap hapus' : 'Belum valid' }}
            </div>
        </div>
        <div class="mt-3 flex items-center justify-end gap-2">
            <label class="mr-auto inline-flex items-start gap-2 text-xs text-red-800 bg-white/80 border border-red-200 rounded-lg px-3 py-2">
                <input type="checkbox" wire:model.live="clearDangerAcknowledged" class="mt-0.5 rounded border-red-300 text-red-600 focus:ring-red-500">
                <span>Saya memahami tindakan ini akan menghapus <span class="font-semibold">semua data absensi karyawan</span> dan tidak bisa dibatalkan.</span>
            </label>
            <button wire:click="closeClearSection" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">Tutup</button>
            <button
                wire:click="clearAllEmployeeAttendance"
                wire:loading.attr="disabled"
                onclick="return confirm('Lanjutkan hapus seluruh data absensi?')"
                @disabled(trim((string) $clearConfirmation) !== 'HAPUS ABSENSI' || !$clearDangerAcknowledged)
                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700"
            >
                Hapus Sekarang
            </button>
        </div>
    </div>
    @endif

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-md">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="whitespace-pre-line">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if($this->lastAttendanceOperation)
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-md text-sm">
            <div class="font-semibold mb-1">Aktivitas proses absensi terakhir</div>
            <div>
                {{ $this->lastAttendanceOperation->created_at?->format('d/m/Y H:i:s') }}
                oleh {{ $this->lastAttendanceOperation->causer?->name ?? 'System' }}
                | aksi: {{ $this->lastAttendanceOperation->properties['action'] ?? '-' }}
                @if(isset($this->lastAttendanceOperation->properties['processed_count']))
                    | processed: {{ $this->lastAttendanceOperation->properties['processed_count'] }}
                @endif
                @if(isset($this->lastAttendanceOperation->properties['error_count']))
                    | errors: {{ $this->lastAttendanceOperation->properties['error_count'] }}
                @endif
                @if(isset($this->lastAttendanceOperation->properties['deleted_count']))
                    | deleted: {{ $this->lastAttendanceOperation->properties['deleted_count'] }}
                @endif
            </div>
        </div>
    @endif

    @if($currentTab === 'daily-correction')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Workbench Koreksi Harian</h3>
                <p class="text-xs text-gray-500">Gunakan untuk karyawan yang lupa absen, dinas luar, sakit, cuti, atau perlu koreksi jam.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Koreksi</label>
                <input type="date" wire:model.live="correctionDate" class="w-full border border-gray-300 rounded-lg px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama/NIP</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama atau NIP..." class="w-full border border-gray-300 rounded-lg px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                <select wire:model.live="unitKerja" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Semua Unit</option>
                    @foreach($this->unitKerjaList as $unit)
                        <option value="{{ $unit }}">{{ $unit }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status (Filter)</label>
                <select wire:model.live="correctionStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Semua</option>
                    <option value="absent">Belum Absen / Tidak Hadir</option>
                    <option value="on_time">Hadir</option>
                    <option value="late">Terlambat</option>
                    <option value="incomplete">Tidak Lengkap</option>
                    <option value="sick">Sakit</option>
                    <option value="leave">Cuti</option>
                    <option value="permission">Izin</option>
                </select>
            </div>
        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-800">
            Gunakan tombol <span class="font-semibold">Tambah Absensi</span> pada header untuk menambahkan koreksi manual baru.
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Karyawan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Masuk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pulang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($dailyCorrections as $row)
                        <tr class="hover:bg-blue-50/30">
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-gray-900">{{ $row['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $row['nip'] ?: 'Tanpa NIP' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['unit_kerja'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['check_in'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['check_out'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if(in_array($row['status'], ['on_time','present','early_arrival'])) bg-green-100 text-green-800
                                    @elseif($row['status']==='late') bg-yellow-100 text-yellow-800
                                    @elseif($row['status']==='sick') bg-gray-100 text-gray-700
                                    @elseif($row['status']==='leave') bg-indigo-100 text-indigo-700
                                    @elseif($row['status']==='permission') bg-blue-100 text-blue-700
                                    @elseif($row['status']==='incomplete') bg-purple-100 text-purple-700
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2 justify-end">
                                    <button wire:click="editByEmployee({{ $row['employee_id'] }})" class="px-2.5 py-1 text-xs rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50">Edit Detail</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">Tidak ada data karyawan untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($currentTab === 'history')
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Riwayat Record Absensi</h3>
            <p class="text-xs text-gray-500">Telusuri record absensi yang sudah tersimpan untuk pengecekan dan koreksi lanjutan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama atau NIP..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Awal</label>
                <input 
                    type="date" 
                    wire:model.live="dateFrom"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input 
                    type="date" 
                    wire:model.live="dateTo"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select 
                    wire:model.live="status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="on_time">Tepat Waktu</option>
                    <option value="early_arrival">Datang Lebih Awal</option>
                    <option value="late">Terlambat</option>
                    <option value="absent">Tidak Hadir</option>
                    <option value="sick">Sakit</option>
                    <option value="leave">Cuti</option>
                    <option value="permission">Izin</option>
                    <option value="incomplete">Absen Tidak Lengkap</option>
                </select>
            </div>

            <div class="flex items-end">
                <button 
                    wire:click="resetFilters"
                    class="w-full px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($currentTab === 'history')
    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Karyawan
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Waktu Masuk
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Waktu Pulang
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-9 w-9 rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                                    {{ strtoupper(substr(($attendance->employee->full_name_with_title ?? $attendance->employee->name ?? 'U'), 0, 2)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $attendance->employee->full_name_with_title ?? $attendance->employee->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $attendance->employee->nip ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $attendance->formatted_date }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $attendance->formatted_check_in }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $attendance->formatted_check_out }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $attendance->status_badge }}-100 text-{{ $attendance->status_badge }}-800">
                                {{ $attendance->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <button 
                                    wire:click="view({{ $attendance->id }})"
                                    class="px-2.5 py-1 text-xs rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50">
                                    Detail
                                </button>
                                <button 
                                    wire:click="edit({{ $attendance->id }})"
                                    class="px-2.5 py-1 text-xs rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50">
                                    Edit
                                </button>
                                <button 
                                    wire:click="delete({{ $attendance->id }})"
                                    onclick="confirm('Apakah Anda yakin ingin menghapus data absensi ini?') || event.stopImmediatePropagation()"
                                    class="px-2.5 py-1 text-xs rounded-md border border-red-200 text-red-700 hover:bg-red-50">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                            Tidak ada record absensi pada filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            {{ $attendances->links() }}
        </div>
    </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showCreateModal || $showEditModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="save">
                    <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">
                            {{ $showEditModal ? 'Edit Data Absensi' : 'Tambah Data Absensi' }}
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Karyawan</label>
                                <select wire:model="user_id" class="mt-1 block w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Pilih Karyawan</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee['id'] }}">{{ $employee['full_name_with_title'] ?? $employee['name'] }} ({{ $employee['nip'] }})</option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                <input type="date" wire:model="date" class="mt-1 block w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Waktu Masuk</label>
                                    <input type="time" wire:model="check_in_time" class="mt-1 block w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('check_in_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Waktu Pulang</label>
                                    <input type="time" wire:model="check_out_time" class="mt-1 block w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('check_out_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select wire:model="status_form" class="mt-1 block w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Pilih Status</option>
                                    <option value="on_time">Hadir</option>
                                    <option value="early_arrival">Datang Lebih Awal</option>
                                    <option value="late">Terlambat</option>
                                    <option value="sick">Sakit</option>
                                    <option value="leave">Cuti</option>
                                    <option value="permission">Izin</option>
                                    <option value="absent">Mangkir</option>
                                    <option value="incomplete">Absen Tidak Lengkap</option>
                                    <option value="checkout-only">Cek Pulang Saja</option>
                                </select>
                                @error('status_form') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Catatan</label>
                                <textarea wire:model="notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent px-4 py-2 bg-blue-600 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto">
                            Simpan
                        </button>
                        <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 px-4 py-2 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Detail Modal -->
    @if($showDetailModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">Detail Absensi</h3>
                </div>
                
                <div class="p-6 space-y-4">
                    <!-- Status & Hours -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Status</label>
                            <span class="mt-1 px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-{{ $this->getAttendanceStatusBadge() ?? 'gray' }}-100 text-{{ $this->getAttendanceStatusBadge() ?? 'gray' }}-800">
                                {{ $this->getAttendanceStatusLabel() ?? 'N/A' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Total Jam Kerja</label>
                            <p class="mt-1 text-lg font-bold text-gray-900">{{ $this->getAttendanceTotalHoursFormatted() ?? '0:00' }}</p>
                        </div>
                    </div>
                    
                    <!-- Shift Info -->
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h4 class="text-sm font-bold text-slate-800 tracking-tight">Informasi Shift</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama Shift</span>
                                <span class="block text-sm font-semibold text-slate-700 mt-0.5">{{ $this->getAttendanceShiftName() }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Jam Kerja</span>
                                <span class="block text-sm font-semibold text-slate-700 mt-0.5">{{ $this->getAttendanceShiftTime() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Loyalty Metrics -->
                    <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-xl">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <h4 class="text-sm font-bold text-emerald-800 tracking-tight">Metrik Loyalitas</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-emerald-100 flex flex-col items-center">
                                <span class="text-xl font-black text-emerald-600 tabular-nums">{{ $this->getAttendanceEarlyFormatted() }}</span>
                                <span class="text-xs font-semibold text-emerald-800/70 uppercase tracking-wide mt-1">Datang Lebih Awal</span>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm border border-orange-100 flex flex-col items-center">
                                <span class="text-xl font-black text-orange-500 tabular-nums">{{ $this->getAttendanceOvertimeFormatted() }}</span>
                                <span class="text-xs font-semibold text-orange-800/70 uppercase tracking-wide mt-1">Jam Lembur</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Catatan</label>
                        <p class="mt-1 text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $this->getAttendanceNotes() ?: 'Tidak ada catatan' }}</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button 
                        type="button" 
                        wire:click="closeModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Unmapped Logs Modal -->
    @if($showUnmappedModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                <!-- Header -->
                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">Mapping PIN Absensi</h3>
                    <p class="text-xs text-yellow-50 mt-1">Hubungkan PIN yang belum terdaftar ke karyawan yang sesuai.</p>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto max-h-[60vh]">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">PIN</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Total Log</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-40">Terakhir Dilihat</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-40">Mesin</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mapping ke Karyawan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($this->groupedUnmappedLogs as $log)
                                <tr class="hover:bg-gray-50" wire:key="log-{{ $log['pin'] }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-lg font-bold text-gray-900 bg-gray-100 px-3 py-1 rounded-lg border border-gray-200">{{ $log['pin'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ $log['total'] }} Logs
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($log['last_seen'])->format('d/m/Y H:i') }}
                                        <div class="text-xs text-gray-400 mt-1">Log Terakhir</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log['mesin'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <select 
                                                wire:model="mappingUserIds.{{ $log['pin'] }}" 
                                                class="block w-full max-w-xs pl-3 pr-10 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg"
                                            >
                                                <option value="">Pilih Karyawan...</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee['id'] }}">
                                                        {{ $employee['full_name_with_title'] ?? $employee['name'] }} 
                                                        @if(!empty($employee['nip']))
                                                        ({{ $employee['nip'] }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button 
                                                type="button" 
                                                wire:click="mapPin('{{ $log['pin'] }}')"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-wait"
                                            >
                                                <span wire:loading.remove wire:target="mapPin('{{ $log['pin'] }}')">
                                                    Simpan
                                                </span>
                                                <span wire:loading wire:target="mapPin('{{ $log['pin'] }}')">
                                                    ...
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-green-100 rounded-full p-3 mb-3">
                                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900">Semua Mapping Aman!</h3>
                                            <p class="text-gray-500 mt-1">Tidak ada data absensi yang belum terhubung ke karyawan.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
