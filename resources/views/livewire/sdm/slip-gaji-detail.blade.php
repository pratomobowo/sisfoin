<div>
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Slip Gaji</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $header->periode }} - {{ $header->keterangan ?? 'Slip Gaji' }}</p>
            </div>
            <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-3">
                <a href="{{ route('sdm.slip-gaji.index') }}" 
                   class="border border-gray-500 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali</span>
                </a>

                <div class="h-8 w-px bg-gray-200 mx-1 hidden sm:block"></div>

                <!-- Queue Management -->
                <livewire:sdm.queue-management />
                
                <button wire:click="confirmBulkEmailSend" 
                        class="bg-green-600 text-white hover:bg-green-700 px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Kirim Email Masal</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Search -->
            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           placeholder="Cari nama, NIP..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Urutan Filter -->
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                <select wire:model.live="sort"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="nama">Nama (A-Z)</option>
                    <option value="nip">NIP</option>
                    <option value="penerimaan_bersih_desc">Penerimaan Bersih (Tertinggi)</option>
                    <option value="penerimaan_bersih_asc">Penerimaan Bersih (Terendah)</option>
                    <option value="potongan_desc">Potongan (Tertinggi)</option>
                    <option value="potongan_asc">Potongan (Terendah)</option>
                </select>
            </div>
            
            <!-- Filter No Email -->
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter</label>
                <div class="flex items-center h-10">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="filterNoEmail" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">No Email</span>
                    </label>
                </div>
            </div>
            
            <!-- Reset Filter Button -->
            <div class="md:col-span-1 flex items-end">
                <button wire:click="resetFilters"
                        class="w-10 h-10 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors flex items-center justify-center"
                        title="Reset Filter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Karyawan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Identitas
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Potongan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Penerimaan Bersih
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($details as $index => $detail)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        @if($detail->employee)
                                            {{ trim(($detail->employee->gelar_depan ? $detail->employee->gelar_depan . ' ' : '') . 
                                                   $detail->employee->nama . 
                                                   ($detail->employee->gelar_belakang ? ', ' . $detail->employee->gelar_belakang : '')) }}
                                        @elseif($detail->dosen)
                                            {{ trim(($detail->dosen->gelar_depan ? $detail->dosen->gelar_depan . ' ' : '') . 
                                                   $detail->dosen->nama . 
                                                   ($detail->dosen->gelar_belakang ? ', ' . $detail->dosen->gelar_belakang : '')) }}
                                        @else
                                            <span class="text-gray-500">Data tidak ditemukan</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if($detail->employee)
                                            @if($detail->employee->email_kampus)
                                                {{ $detail->employee->email_kampus }}
                                                <span class="text-xs text-gray-400">(kampus)</span>
                                            @elseif($detail->employee->email)
                                                {{ $detail->employee->email }}
                                                <span class="text-xs text-gray-400">(pribadi)</span>
                                            @else
                                                <span class="text-red-500">Tidak ada email</span>
                                            @endif
                                        @elseif($detail->dosen)
                                            @if($detail->dosen->email_kampus)
                                                {{ $detail->dosen->email_kampus }}
                                                <span class="text-xs text-gray-400">(kampus)</span>
                                            @elseif($detail->dosen->email)
                                                {{ $detail->dosen->email }}
                                                <span class="text-xs text-gray-400">(pribadi)</span>
                                            @else
                                                <span class="text-red-500">Tidak ada email</span>
                                            @endif
                                        @else
                                            <span class="text-red-500">Tidak ada email</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $detail->nip }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-red-600">
                                    Rp {{ number_format($detail->total_potongan ?: 0, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">Rp {{ number_format($detail->penerimaan_bersih, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-1">
                                    <!-- Edit -->
                                    <a href="{{ route('sdm.slip-gaji.edit', $detail->id) }}" 
                                       title="Edit Slip Gaji" aria-label="Edit Slip Gaji"
                                       class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-yellow-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <!-- Preview PDF -->
                                    <button type="button" 
                                            onclick="previewPdf('{{ route('sdm.slip-gaji.preview-pdf-slip', $detail->id) }}')"
                                            title="Preview Slip Gaji" aria-label="Preview Slip Gaji"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-blue-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <!-- Send Email -->
                                    <button wire:click="confirmEmailSend({{ $detail->id }})" 
                                            title="Kirim Email" aria-label="Kirim Email"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-green-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada data slip gaji ditemukan</h3>
                                    <p class="text-sm text-gray-500">Tidak ada data slip gaji yang ditemukan dengan filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($details->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-white">
                <x-superadmin.pagination 
                    :currentPage="$details->currentPage()"
                    :lastPage="$details->lastPage()"
                    :total="$details->total()"
                    :perPage="$details->perPage()"
                    :perPageOptions="[10, 25, 50, 100]"
                    :showPageInfo="true"
                    :showPerPage="true"
                    :alignment="'justify-between'"
                    perPageWireModel="perPage"
                    previousPageWireModel="previousPage"
                    nextPageWireModel="nextPage"
                    gotoPageWireModel="gotoPage" />
            </div>
        @endif
    </div>

    <!-- PDF Preview Modal -->
    <div id="pdfModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closePdfModal()"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[95vh] flex flex-col">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                        Preview Slip Gaji
                    </h3>
                    <button type="button" onclick="closePdfModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-lg p-1 hover:bg-gray-100 transition-colors">
                        <span class="sr-only">Tutup</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-2 overflow-hidden" style="height: 50vh;">
                    <iframe id="pdfFrame" src="" class="w-full h-full border-0 rounded"></iframe>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex justify-end p-3 border-t border-gray-200 bg-gray-50">
                    <button type="button" onclick="closePdfModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors font-medium text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Confirmation Modal -->
    <div x-data="{ 
        show: @entangle('confirmingEmailSend').live,
        employeeName: @entangle('confirmingEmployeeName').live 
    }" 
         x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full">
                <!-- Modal Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Konfirmasi Kirim Email
                            </h3>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600">
                                Apakah Anda yakin ingin mengirim slip gaji kepada:
                            </p>
                            <p class="mt-2 text-base font-medium text-gray-900" x-text="employeeName"></p>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-800">
                                        Pastikan email karyawan valid dan sudah terisi dengan benar sebelum mengirim.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
                    <button wire:click="cancelEmailSend" 
                            type="button" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors font-medium text-sm">
                        Batal
                    </button>
                    <button wire:click="sendSingleEmail" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>Kirim Email</span>
                        <span wire:loading>
                            <svg class="w-4 h-4 animate-spin inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Mengirim...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Email Confirmation Modal -->
    <div x-data="{ 
        show: @entangle('confirmingBulkEmailSend').live
    }" 
         x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full">
                <!-- Modal Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Konfirmasi Kirim Email Masal
                            </h3>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600">
                                Apakah Anda yakin ingin mengirim slip gaji kepada semua karyawan yang terdaftar untuk periode:
                            </p>
                            <p class="mt-2 text-base font-medium text-gray-900">
                                {{ $header->periode }}
                            </p>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-800">
                                        Email akan dikirim ke semua karyawan yang memiliki alamat email valid. 
                                        Sistem akan menggunakan email kampus sebagai prioritas utama.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-800">
                                        Pastikan semua data karyawan dan email sudah valid sebelum melanjutkan. 
                                        Proses ini memakan waktu beberapa saat.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
                    <button wire:click="cancelBulkEmailSend" 
                            type="button" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors font-medium text-sm">
                        Batal
                    </button>
                    <button wire:click="sendBulkEmailWithConfirmation" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>Kirim Email Masal</span>
                        <span wire:loading>
                            <svg class="w-4 h-4 animate-spin inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Mengirim...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewPdf(url) {
            event.preventDefault();
            event.stopPropagation();
            
            const modal = document.getElementById('pdfModal');
            const iframe = document.getElementById('pdfFrame');
            
            // Set iframe source first
            iframe.src = url;
            
            // Show modal
            modal.classList.remove('hidden');
            
            // Prevent body scrolling
            document.body.style.overflow = 'hidden';
            
            // Focus on modal for accessibility
            modal.focus();
        }

        function closePdfModal() {
            const modal = document.getElementById('pdfModal');
            const iframe = document.getElementById('pdfFrame');
            
            // Hide modal
            modal.classList.add('hidden');
            
            // Clear iframe source
            iframe.src = '';
            
            // Restore body scrolling
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('pdfModal');
            if (event.target === modal) {
                closePdfModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('pdfModal');
                if (!modal.classList.contains('hidden')) {
                    closePdfModal();
                }
            }
        });
    </script>
</div>
