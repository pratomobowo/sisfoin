<div>
    <div class="space-y-6">
    <!-- Header -->
    <x-page-header 
        title="Data Absensi Fingerprint" 
        subtitle="Daftar data absensi dari mesin fingerprint"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Administrasi' => '#', 'Log Absensi' => route('superadmin.fingerprint.attendance-logs.index')]"
    >
        <x-slot name="actions">
            <x-button variant="primary" wire:click="openPullDataModal">
                <x-lucide-download class="w-4 h-4 mr-2" />
                <span>Tarik Data</span>
            </x-button>
            <x-button variant="danger" wire:click="confirmClearAll">
                <x-lucide-trash-2 class="w-4 h-4 mr-2" />
                <span>Hapus Semua</span>
            </x-button>
        </x-slot>
    </x-page-header>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Mesin Fingerprint -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mesin Fingerprint</label>
                <select wire:model.live="mesinFingerId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Mesin</option>
                    @foreach($mesinFingers as $mesin)
                        <option value="{{ $mesin->id }}">{{ $mesin->nama_mesin }} ({{ $mesin->ip_address }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" 
                       wire:model.live="date"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- PIN / Nama -->
            <div class="md:col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">PIN / Nama</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-lucide-search class="w-4 h-4 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="pin"
                           type="text"
                           placeholder="Cari PIN atau Nama..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            
            <!-- Reset Filter Button -->
            <div class="md:col-span-1 flex items-end justify-end">
                <button wire:click="resetFilters"
                        class="w-10 h-10 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors flex items-center justify-center"
                        title="Reset Filter">
                    <x-lucide-rotate-ccw class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <x-lucide-clipboard-list class="w-6 h-6 text-blue-600" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Records</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $attendanceLogs->total() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <x-lucide-clock class="w-6 h-6 text-green-600" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Tanggal Terbaru</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $attendanceLogs->isNotEmpty() ? $attendanceLogs->first()->datetime->format('d/m/Y H:i:s') : '-' }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <x-lucide-users class="w-6 h-6 text-purple-600" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total PIN Unik</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $attendanceLogs->count() > 0 ? $attendanceLogs->unique('pin')->count() : 0 }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Data Absensi</h3>
            <div class="mt-2 sm:mt-0">
                <p class="text-sm text-gray-600">
                    Menampilkan {{ $attendanceLogs->firstItem() }} sampai {{ $attendanceLogs->lastItem() }} dari {{ $attendanceLogs->total() }} records
                </p>
            </div>
        </div>

        @if($attendanceLogs->isEmpty())
            <div class="text-center py-12">
                <x-lucide-clipboard-x class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data absensi</h3>
                <p class="mt-1 text-sm text-gray-500">Belum ada data absensi yang tersimpan di database.</p>
                <div class="mt-6">
                    <a href="{{ route('superadmin.fingerprint.attendance-logs.index') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Tarik Data Sekarang</span>
                    </a>
                </div>
            </div>
        @else
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            PIN
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Nama
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Mesin
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Waktu
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Verifikasi
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($attendanceLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $log->pin ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->mesinFinger->nama_mesin ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->datetime ? $log->datetime->format('d/m/Y H:i:s') : '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $log->status ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->verify ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Detail -->
                                    <a href="{{ route('superadmin.fingerprint.attendance-logs.show', $log) }}" 
                                           title="Detail" aria-label="Detail"
                                           class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-blue-600">
                                        <x-lucide-eye class="w-5 h-5" />
                                    </a>

                                    <!-- Delete -->
                                    <button wire:click="confirmDelete({{ $log->id }})" 
                                            title="Hapus" aria-label="Hapus"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-red-600">
                                        <x-lucide-trash-2 class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

            <!-- Pagination -->
            @if($attendanceLogs->hasPages())
                <div class="mt-6">
                    <x-superadmin.pagination 
                        :currentPage="$attendanceLogs->currentPage()"
                        :lastPage="$attendanceLogs->lastPage()"
                        :total="$attendanceLogs->total()"
                        :perPage="$attendanceLogs->perPage()"
                        :showPageInfo="true"
                        :showPerPage="false"
                        alignment="justify-between"
                        previousPageWireModel="previousPage"
                        nextPageWireModel="nextPage"
                        gotoPageWireModel="gotoPage"
                    />
                </div>
            @endif
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
@if($showDeleteModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <x-lucide-alert-triangle class="h-6 w-6 text-red-600" />
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Konfirmasi Hapus</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus data absensi ini?</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button wire:click="deleteAttendanceLog" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg w-24 mr-2 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Ya
                    </button>
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 bg-gray-500 text-white text-sm font-semibold rounded-lg w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Tidak
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
    </div>

<!-- Pull Data Modal -->
@if($showPullDataModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 max-w-md w-full">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <x-lucide-download class="w-5 h-5 text-blue-600" />
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-900">Tarik Data Fingerprint</h3>
                </div>
                <button wire:click="$set('showPullDataModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <x-lucide-x class="w-6 h-6" />
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-6">Data akan ditarik secara terpusat dari ADMS API yang mencakup semua mesin fingerprint yang aktif.</p>

                <!-- Pull Options -->
                <div class="border-t border-gray-200 pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Opsi Tarik Data:</label>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="radio" wire:model="pullOption" value="all" id="pull_all" class="h-4 w-4 text-blue-600 focus:ring-2 focus:ring-blue-500 border-gray-300">
                            <label for="pull_all" class="ml-3 block text-sm text-gray-700">Tarik semua data</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" wire:model="pullOption" value="today" id="pull_today" class="h-4 w-4 text-blue-600 focus:ring-2 focus:ring-blue-500 border-gray-300">
                            <label for="pull_today" class="ml-3 block text-sm text-gray-700">Tarik data hari ini</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" wire:model="pullOption" value="range" id="pull_range" class="h-4 w-4 text-blue-600 focus:ring-2 focus:ring-blue-500 border-gray-300">
                            <label for="pull_range" class="ml-3 block text-sm text-gray-700">Tarik data berdasarkan tanggal</label>
                        </div>
                    </div>
                    
                    <!-- Date Range Fields (always visible when pullOption is range) -->
                    <div wire:show="pullOption === 'range'" class="mt-4 grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Dari Tanggal</label>
                            <input type="date" wire:model="pullDateFrom" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Sampai Tanggal</label>
                            <input type="date" wire:model="pullDateTo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                    </div>
                    
                    <!-- Auto Processing Option -->
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="autoProcessAttendances" id="auto_process" class="h-4 w-4 text-blue-600 focus:ring-2 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="auto_process" class="ml-3 block text-sm text-gray-700">
                                <span class="font-medium">Otomatis proses ke absensi karyawan</span>
                                <span class="text-gray-500 block text-xs mt-0.5">Data akan langsung dimasukkan ke tabel absensi karyawan</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                @if($isPullingData)
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center">
                            <x-lucide-loader-2 class="animate-spin h-5 w-5 text-blue-600 mr-3" />
                            <div>
                                <p class="text-sm font-medium text-blue-800">Sedang menarik data...</p>
                                <p class="text-xs text-blue-600">{{ $pullProgressMessage }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Results Display -->
                @if(!empty($pullResults) && !$isPullingData)
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Hasil Tarik Data:</h4>
                        <div class="space-y-3 max-h-60 overflow-y-auto">
                            @foreach($pullResults as $result)
                                <div class="p-3 rounded-lg border @if($result['success']) border-green-200 bg-green-50 @else border-red-200 bg-red-50 @endif">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <span class="font-medium text-sm @if($result['success']) text-green-800 @else text-red-800 @endif">
                                                    ADMS API Response
                                                </span>
                                            </div>
                                            <p class="text-xs mt-1 @if($result['success']) text-green-700 @else text-red-700 @endif">
                                                {{ $result['message'] }}
                                            </p>
                                        </div>
                                        <div class="ml-3 flex-shrink-0">
                                            @if($result['success'])
                                                <x-lucide-check-circle class="w-5 h-5 text-green-500" />
                                            @else
                                                <x-lucide-x-circle class="w-5 h-5 text-red-500" />
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
                <button wire:click="$set('showPullDataModal', false)" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold rounded-lg transition-colors">
                    Batal
                </button>
                <button wire:click="pullFingerprintData" wire:loading.attr="disabled" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-50 flex items-center space-x-2">
                    <span wire:loading.remove>Tarik Data</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Clear All Confirmation Modal -->
@if($showClearAllModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <x-lucide-alert-triangle class="h-6 w-6 text-red-600" />
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Konfirmasi Hapus Semua</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus SEMUA data absensi? Tindakan ini tidak dapat dibatalkan!</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button wire:click="clearAllAttendanceLogs" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg w-24 mr-2 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Ya
                    </button>
                    <button wire:click="$set('showClearAllModal', false)" class="px-4 py-2 bg-gray-500 text-white text-sm font-semibold rounded-lg w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Tidak
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
