<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Kegiatan Pejabat</h2>
                <p class="mt-1 text-sm text-gray-600">Kelola data kegiatan yang dilakukan oleh pejabat universitas</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <button wire:click="create" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Kegiatan Pejabat</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
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
                           placeholder="Cari nama kegiatan, tempat, pejabat..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Jenis Kegiatan Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Jenis Kegiatan</label>
                <select wire:model.live="filterJenis"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisKegiatanOptions as $jenis)
                        <option value="{{ $jenis }}">{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Pejabat Filter -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Pejabat</label>
                <select wire:model.live="filterPejabat"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Pejabat</option>
                    @foreach($pejabatList as $pejabat)
                        <option value="{{ $pejabat['id'] }}">{{ $pejabat['jabatan'] }}</option>
                    @endforeach
                </select>
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

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Kegiatan Pejabat</h3>
            <div class="mt-2 sm:mt-0">
                <p class="text-sm text-gray-600">
                    Total Kegiatan: <span class="font-medium">{{ $kegiatanPejabat->total() }}</span>
                </p>
            </div>
        </div>
        
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama Kegiatan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jenis & Tempat
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pejabat Terkait
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Disposisi
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($kegiatanPejabat as $kegiatan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $kegiatan->nama_kegiatan }}</div>
                                <div class="text-xs text-gray-500">{{ Str::limit($kegiatan->keterangan, 50) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $kegiatan->jenis_kegiatan }}</div>
                                <div class="text-xs text-gray-500">{{ $kegiatan->tempat_kegiatan }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $kegiatan->formatted_tanggal_range }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $kegiatan->pejabat_terkait_names }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $kegiatan->disposisi_kepada }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-1">
                                    <!-- View -->
                                    <button wire:click="view({{ $kegiatan->id }})" 
                                            title="Lihat Detail" aria-label="Lihat Detail"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <!-- Download -->
                                    @if($kegiatan->hasFile())
                                    <button wire:click="download({{ $kegiatan->id }})" 
                                            title="Download Lampiran" aria-label="Download Lampiran"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-green-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </button>
                                    @endif
                                    <!-- Edit -->
                                    <button wire:click="edit({{ $kegiatan->id }})" 
                                            title="Edit" aria-label="Edit"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-yellow-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <!-- Delete -->
                                    <button 
                                            title="Hapus" aria-label="Hapus"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-red-600"
                                            onclick="confirm('Apakah Anda yakin ingin menghapus kegiatan pejabat ini?') && @this.call('delete', {{ $kegiatan->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707v11a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada data kegiatan pejabat ditemukan</h3>
                                    <p class="text-sm text-gray-500">Silakan tambahkan kegiatan pejabat baru</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($kegiatanPejabat->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium">{{ $kegiatanPejabat->firstItem() }}</span> hingga
                        <span class="font-medium">{{ $kegiatanPejabat->lastItem() }}</span> dari
                        <span class="font-medium">{{ $kegiatanPejabat->total() }}</span> hasil
                    </div>
                    
                    <div class="flex items-center space-x-1">
                        {{-- Previous Button --}}
                        @if($kegiatanPejabat->onFirstPage())
                            <button disabled class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @else
                            <button wire:click="previousPage" class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                        @endif
                        
                        {{-- Page Numbers --}}
                        @php
                            $currentPage = $kegiatanPejabat->currentPage();
                            $lastPage = $kegiatanPejabat->lastPage();
                            
                            // Show limited page numbers
                            $maxPageLinks = 5;
                            $startPage = max(1, $currentPage - floor($maxPageLinks / 2));
                            $endPage = min($lastPage, $startPage + $maxPageLinks - 1);
                            
                            // Adjust start page if we're near the end
                            if ($endPage - $startPage < $maxPageLinks - 1) {
                                $startPage = max(1, $endPage - $maxPageLinks + 1);
                            }
                        @endphp
                        
                        {{-- First page and ellipsis if needed --}}
                        @if($startPage > 1)
                            <button wire:click="gotoPage(1)" class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                                1
                            </button>
                            @if($startPage > 2)
                                <span class="relative inline-flex items-center px-2 py-2 text-sm text-gray-500">...</span>
                            @endif
                        @endif
                        
                        {{-- Page numbers --}}
                        @for($i = $startPage; $i <= $endPage; $i++)
                            @if($i == $currentPage)
                                <button class="relative inline-flex items-center px-3 py-2 border border-blue-500 bg-blue-500 text-sm font-medium text-white rounded-md">
                                    {{ $i }}
                                </button>
                            @else
                                <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                                    {{ $i }}
                                </button>
                            @endif
                        @endfor
                        
                        {{-- Last page and ellipsis if needed --}}
                        @if($endPage < $lastPage)
                            @if($endPage < $lastPage - 1)
                                <span class="relative inline-flex items-center px-2 py-2 text-sm text-gray-500">...</span>
                            @endif
                            <button wire:click="gotoPage({{ $lastPage }})" class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                                {{ $lastPage }}
                            </button>
                        @endif
                        
                        {{-- Next Button --}}
                        @if($kegiatanPejabat->hasMorePages())
                            <button wire:click="nextPage" class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @else
                            <button disabled class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCreateModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Tambah Kegiatan Pejabat
                                </h3>
                                <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="max-h-[70vh] overflow-y-auto">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Nama Kegiatan -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                                        <input wire:model.live="namaKegiatan" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Nama kegiatan">
                                        @error('namaKegiatan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Jenis Kegiatan -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kegiatan <span class="text-red-500">*</span></label>
                                        <select wire:model.live="jenisKegiatan" 
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Pilih Jenis Kegiatan</option>
                                            @foreach($jenisKegiatanOptions as $jenis)
                                                <option value="{{ $jenis }}">{{ $jenis }}</option>
                                            @endforeach
                                        </select>
                                        @error('jenisKegiatan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tempat Kegiatan -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Kegiatan <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tempatKegiatan" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Tempat kegiatan">
                                        @error('tempatKegiatan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tanggal Mulai -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tanggalMulai" type="date" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('tanggalMulai')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tanggal Selesai -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tanggalSelesai" type="date" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('tanggalSelesai')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Pejabat Terkait -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pejabat Terkait <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($pejabatList as $pejabat)
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" 
                                                           value="{{ $pejabat['id'] }}" 
                                                           wire:model.live="pejabatTerkait"
                                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $pejabat['display'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('pejabatTerkait')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Disposisi Kepada -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Disposisi Kepada</label>
                                        <input wire:model.live="disposisiKepada" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Pihak yang dituju disposisi">
                                        @error('disposisiKepada')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- File Upload -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">File Lampiran</label>
                                        <input wire:model.live="file" type="file"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Format file: PDF, DOC, DOCX, JPG, JPEG, PNG. Maksimal 10MB.</p>
                                        @error('file')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @endif
                                        @if($file)
                                            <p class="mt-2 text-sm text-gray-600">File: {{ $file->getClientOriginalName() }}</p>
                                        @endif
                                    </div>

                                    <!-- Keterangan -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                                        <textarea wire:model.live="keterangan" rows="3"
                                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  placeholder="Keterangan tambahan (opsional)"></textarea>
                                        @error('keterangan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button type="button" wire:click="closeCreateModal" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEditModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form wire:submit="update">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Edit Kegiatan Pejabat
                                </h3>
                                <button type="button" wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="max-h-[70vh] overflow-y-auto">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Nama Kegiatan -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                                        <input wire:model.live="namaKegiatan" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Nama kegiatan">
                                        @error('namaKegiatan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Jenis Kegiatan -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kegiatan <span class="text-red-500">*</span></label>
                                        <select wire:model.live="jenisKegiatan" 
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Pilih Jenis Kegiatan</option>
                                            @foreach($jenisKegiatanOptions as $jenis)
                                                <option value="{{ $jenis }}">{{ $jenis }}</option>
                                            @endforeach
                                        </select>
                                        @error('jenisKegiatan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tempat Kegiatan -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Kegiatan <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tempatKegiatan" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Tempat kegiatan">
                                        @error('tempatKegiatan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tanggal Mulai -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tanggalMulai" type="date" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('tanggalMulai')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tanggal Selesai -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tanggalSelesai" type="date" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('tanggalSelesai')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Pejabat Terkait -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pejabat Terkait <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($pejabatList as $pejabat)
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" 
                                                           value="{{ $pejabat['id'] }}" 
                                                           wire:model.live="pejabatTerkait"
                                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $pejabat['display'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('pejabatTerkait')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Disposisi Kepada -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Disposisi Kepada</label>
                                        <input wire:model.live="disposisiKepada" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Pihak yang dituju disposisi">
                                        @error('disposisiKepada')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- File Upload -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">File Lampiran (Opsional)</label>
                                        <input wire:model.live="file" type="file"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Format file: PDF, DOC, DOCX, JPG, JPEG, PNG. Maksimal 10MB. Kosongkan jika tidak ingin mengganti file.</p>
                                        @error('file')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @endif
                                        @if($file)
                                            <p class="mt-2 text-sm text-gray-600">File baru: {{ $file->getClientOriginalName() }}</p>
                                        @endif
                                    </div>

                                    <!-- Keterangan -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                                        <textarea wire:model.live="keterangan" rows="3"
                                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  placeholder="Keterangan tambahan (opsional)"></textarea>
                                        @error('keterangan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Update
                            </button>
                            <button type="button" wire:click="closeEditModal" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal && $viewKegiatan)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeViewModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Detail Kegiatan Pejabat
                            </h3>
                            <button type="button" wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Informasi Umum -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Umum</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Kegiatan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->nama_kegiatan }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jenis Kegiatan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->jenis_kegiatan }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tempat Kegiatan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->tempat_kegiatan }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->formatted_tanggal_range }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Pejabat dan Disposisi -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Pejabat</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Pejabat Terkait:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->pejabat_terkait_names }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Disposisi Kepada:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->disposisi_kepada }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Dibuat Oleh:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->createdBy->name ?? 'Tidak Diketahui' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Dibuat Tanggal:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->created_at->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi File -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi File</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama File:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->file_name }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Ukuran File:</span>
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->formatted_file_size }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Aksi:</span>
                                            <div class="mt-2">
                                                @if($viewKegiatan->hasFile())
                                                <a href="{{ $viewKegiatan->file_url }}" 
                                                   target="_blank"
                                                   class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    Download File
                                                </a>
                                                @else
                                                <span class="text-sm text-gray-500">Tidak ada file lampiran</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Keterangan -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Keterangan</h4>
                                    <div>
                                        @if($viewKegiatan->keterangan)
                                            <p class="text-sm text-gray-900">{{ $viewKegiatan->keterangan }}</p>
                                        @else
                                            <p class="text-sm text-gray-500 italic">Tidak ada keterangan</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeViewModal" 
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>