<div class="space-y-6">
    <x-page-header 
        title="Manajemen Slip Gaji" 
        subtitle="Kelola data slip gaji karyawan per periode"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'SDM' => '#', 'Slip Gaji' => route('sdm.slip-gaji.index')]"
    >
        <x-slot name="actions">
            <x-button variant="primary" :href="route('sdm.slip-gaji.upload')">
                <x-lucide-upload class="w-4 h-4 mr-2" />
                <span>Upload Data Slip Gaji</span>
            </x-button>
        </x-slot>
    </x-page-header>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-lucide-search class="w-4 h-4 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           placeholder="Cari periode, tahun..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Filter Periode -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                <select wire:model.live="filterPeriode"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Periode</option>
                    @foreach($availablePeriodes as $periode)
                        <option value="{{ $periode }}">{{ $periode }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tahun -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select wire:model.live="filterTahun"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Tahun</option>
                    @foreach($availableTahuns as $tahun)
                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Sort & Reset -->
        <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Urutkan:</label>
                <select wire:model.live="sortField"
                        class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="created_at">Tanggal Dibuat</option>
                    <option value="periode">Periode</option>
                    <option value="tahun">Tahun</option>
                </select>
                <select wire:model.live="sortDirection"
                        class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="desc">Terbaru</option>
                    <option value="asc">Terlama</option>
                </select>
            </div>
            <button wire:click="resetFilters" 
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                Reset Filter
            </button>
        </div>

        <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-800">
            Gunakan tombol di header untuk <span class="font-semibold">Upload Data Slip Gaji</span>. Area ini khusus filter dan pencarian agar proses lebih mudah diikuti.
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Periode
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tahun
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Data
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal Dibuat
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($headers as $header)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $header->periode }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($header->periode)
                                        {{ substr($header->periode, 0, 4) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ number_format($header->details_count) }} karyawan</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $header->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-1">
                                    <!-- View -->
                                    <a href="{{ route('sdm.slip-gaji.show', $header->id) }}" 
                                       title="Lihat Detail" aria-label="Lihat Detail"
                                       class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-blue-600">
                                        <x-lucide-eye class="w-4 h-4" />
                                    </a>
                                    <!-- Export Excel -->
                                    <button wire:click="exportExcel({{ $header->id }})" 
                                            title="Export Excel" aria-label="Export Excel"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-green-600">
                                        <x-lucide-file-spreadsheet class="w-4 h-4" />
                                    </button>
                                    <!-- Delete -->
                                    <button wire:click="openDeleteModal({{ $header->id }})" 
                                            title="Hapus" aria-label="Hapus"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-red-600">
                                        <x-lucide-trash-2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <x-lucide-file-text class="w-12 h-12 text-gray-400 mb-4" />
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data slip gaji</h3>
                                    <p class="text-gray-500 mb-4">Mulai dengan mengupload data slip gaji karyawan</p>
                                    <a href="{{ route('sdm.slip-gaji.upload') }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors inline-block">
                                        Upload Data Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($headers->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <x-superadmin.pagination 
                    :paginator="$headers" />
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDeleteModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-lucide-alert-triangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Konfirmasi Hapus
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus data slip gaji ini? Semua data detail slip gaji akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.
                                    </p>
                                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                        Penghapusan akan ditolak otomatis jika masih ada email slip gaji dengan status <span class="font-semibold">pending</span> atau <span class="font-semibold">processing</span>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="deleteSlipGaji" 
                                wire:loading.attr="disabled"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:bg-red-400">
                            <span wire:loading.remove wire:target="deleteSlipGaji">Hapus</span>
                            <span wire:loading wire:target="deleteSlipGaji">Menghapus...</span>
                        </button>
                        <button type="button" wire:click="closeDeleteModal" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
