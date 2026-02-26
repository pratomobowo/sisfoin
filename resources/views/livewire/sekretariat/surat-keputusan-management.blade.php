<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Surat Keputusan</h2>
                <p class="mt-1 text-sm text-gray-600">Kelola data surat keputusan dan dokumen resmi</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <button wire:click="create" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Surat Keputusan</span>
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
                           placeholder="Cari nomor surat, tentang, pejabat..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Tipe Surat Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Tipe Surat</label>
                <select wire:model.live="filterTipeSurat"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Tipe</option>
                    @foreach($tipeSuratOptions as $tipe)
                        <option value="{{ $tipe }}">{{ $tipe }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Kategori SK Filter -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Kategori</label>
                <select wire:model.live="filterKategoriSk"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriSkOptions as $kategori)
                        <option value="{{ $kategori }}">{{ $kategori }}</option>
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
            <h3 class="text-lg font-semibold text-gray-900">Daftar Surat Keputusan</h3>
            <div class="mt-2 sm:mt-0">
                <p class="text-sm text-gray-600">
                    Total Surat: <span class="font-medium">{{ $suratKeputusan->total() }}</span>
                </p>
            </div>
        </div>
        
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nomor Surat
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tentang
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipe & Kategori
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pejabat
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($suratKeputusan as $sk)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $sk->nomor_surat }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $sk->tentang }}</div>
                                @if($sk->deskripsi)
                                    <div class="text-xs text-gray-500 mt-1">{{ Str::limit($sk->deskripsi, 100) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $sk->tipe_surat }}</div>
                                <div class="text-xs text-gray-500">{{ $sk->kategori_sk }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $sk->formatted_tanggal_penetapan }}</div>
                                <div class="text-xs text-gray-500">Berlaku: {{ $sk->formatted_tanggal_berlaku }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $sk->ditandatangani_oleh }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($sk->status_color === 'green') bg-green-100 text-green-800
                                    @elseif($sk->status_color === 'red') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $sk->status_text }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-1">
                                    <!-- View -->
                                    <button wire:click="view({{ $sk->id }})" 
                                            title="Lihat Detail" aria-label="Lihat Detail"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <!-- Download -->
                                    <button wire:click="download({{ $sk->id }})" 
                                            title="Download PDF" aria-label="Download PDF"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-green-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </button>
                                    <!-- Edit -->
                                    <button wire:click="edit({{ $sk->id }})" 
                                            title="Edit" aria-label="Edit"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-yellow-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <!-- Delete -->
                                    <button wire:click="delete({{ $sk->id }})" 
                                            title="Hapus" aria-label="Hapus"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-red-600"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus surat keputusan ini?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707v11a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada data surat keputusan ditemukan</h3>
                                    <p class="text-sm text-gray-500">Silakan tambahkan surat keputusan baru</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($suratKeputusan->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-white">
                <x-superadmin.pagination
                    :currentPage="$suratKeputusan->currentPage()"
                    :lastPage="$suratKeputusan->lastPage()"
                    :total="$suratKeputusan->total()"
                    :perPage="$suratKeputusan->perPage()"
                    :perPageOptions="[10]"
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
                                    Tambah Surat Keputusan
                                </h3>
                                <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="max-h-[70vh] overflow-y-auto">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Nomor Surat -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat <span class="text-red-500">*</span></label>
                                        <input wire:model.live="nomorSurat" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Contoh: SK-001/REKTOR/2025">
                                        @error('nomorSurat')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tentang/Perihal -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tentang/Perihal <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tentang" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Tentang surat keputusan">
                                        @error('tentang')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tipe Surat -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Surat <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input wire:model.live="tipeSurat" type="text" 
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="SK Rektor, SK Dekan, dll"
                                                   list="tipeSuratList">
                                            <datalist id="tipeSuratList">
                                                @foreach($tipeSuratList as $tipe)
                                                    <option value="{{ $tipe }}">{{ $tipe }}</option>
                                                @endforeach
                                            </datalist>
                                        </div>
                                        @error('tipeSurat')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Kategori SK -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori SK <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input wire:model.live="kategoriSk" type="text" 
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Kepegawaian, Akademik, dll"
                                                   list="kategoriSkList">
                                            <datalist id="kategoriSkList">
                                                @foreach($kategoriSkList as $kategori)
                                                    <option value="{{ $kategori }}">{{ $kategori }}</option>
                                                @endforeach
                                            </datalist>
                                        </div>
                                        @error('kategoriSk')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tanggal Penetapan -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Penetapan <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tanggalPenetapan" type="date" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('tanggalPenetapan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tanggal Berlaku -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berlaku <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tanggalBerlaku" type="date" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('tanggalBerlaku')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Ditandatangani Oleh -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ditandatangani Oleh <span class="text-red-500">*</span></label>
                                        <select wire:model.live="ditandatanganiOleh" 
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Pilih Pejabat</option>
                                            @foreach($pejabatList as $pejabat)
                                                <option value="{{ $pejabat['display'] }}">{{ $pejabat['display'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('ditandatanganiOleh')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- File Upload -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">File PDF <span class="text-red-500">*</span></label>
                                        <input wire:model.live="file" type="file" accept=".pdf"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Maksimal 20MB, format PDF saja.</p>
                                        @error('file')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @endif
                                        @if($file)
                                            <p class="mt-2 text-sm text-gray-600">File: {{ $file->getClientOriginalName() }}</p>
                                        @endif
                                    </div>

                                    <!-- Deskripsi -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                        <textarea wire:model.live="deskripsi" rows="3"
                                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  placeholder="Deskripsi tambahan (opsional)"></textarea>
                                        @error('deskripsi')
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
                                    Edit Surat Keputusan
                                </h3>
                                <button type="button" wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="max-h-[70vh] overflow-y-auto">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Nomor Surat -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat <span class="text-red-500">*</span></label>
                                        <input wire:model.live="nomorSurat" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Contoh: SK-001/REKTOR/2025">
                                        @error('nomorSurat')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tentang/Perihal -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tentang/Perihal <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tentang" type="text" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Tentang surat keputusan">
                                        @error('tentang')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tipe Surat -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Surat <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input wire:model.live="tipeSurat" type="text" 
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="SK Rektor, SK Dekan, dll"
                                                   list="tipeSuratList">
                                            <datalist id="tipeSuratList">
                                                @foreach($tipeSuratList as $tipe)
                                                    <option value="{{ $tipe }}">{{ $tipe }}</option>
                                                @endforeach
                                            </datalist>
                                        </div>
                                        @error('tipeSurat')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Kategori SK -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori SK <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input wire:model.live="kategoriSk" type="text" 
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Kepegawaian, Akademik, dll"
                                                   list="kategoriSkList">
                                            <datalist id="kategoriSkList">
                                                @foreach($kategoriSkList as $kategori)
                                                    <option value="{{ $kategori }}">{{ $kategori }}</option>
                                                @endforeach
                                            </datalist>
                                        </div>
                                        @error('kategoriSk')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tanggal Penetapan -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Penetapan <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tanggalPenetapan" type="date" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('tanggalPenetapan')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Tanggal Berlaku -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berlaku <span class="text-red-500">*</span></label>
                                        <input wire:model.live="tanggalBerlaku" type="date" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('tanggalBerlaku')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Ditandatangani Oleh -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ditandatangani Oleh <span class="text-red-500">*</span></label>
                                        <select wire:model.live="ditandatanganiOleh" 
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Pilih Pejabat</option>
                                            @foreach($pejabatList as $pejabat)
                                                <option value="{{ $pejabat['display'] }}">{{ $pejabat['display'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('ditandatanganiOleh')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- File Upload -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">File PDF (Opsional)</label>
                                        <input wire:model.live="file" type="file" accept=".pdf"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Maksimal 20MB, format PDF saja. Kosongkan jika tidak ingin mengganti file.</p>
                                        @error('file')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @endif
                                        @if($file)
                                            <p class="mt-2 text-sm text-gray-600">File baru: {{ $file->getClientOriginalName() }}</p>
                                        @endif
                                    </div>

                                    <!-- Deskripsi -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                        <textarea wire:model.live="deskripsi" rows="3"
                                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  placeholder="Deskripsi tambahan (opsional)"></textarea>
                                        @error('deskripsi')
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
    @if($showViewModal && $viewSuratKeputusan)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeViewModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Detail Surat Keputusan
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
                                            <span class="text-xs font-medium text-gray-500">Nomor Surat:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->nomor_surat }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tentang/Perihal:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->tentang }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tipe Surat:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->tipe_surat }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Kategori SK:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->kategori_sk }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status:</span>
                                            <p class="text-sm text-gray-900">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                    @if($viewSuratKeputusan->status_color === 'green') bg-green-100 text-green-800
                                                    @elseif($viewSuratKeputusan->status_color === 'red') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ $viewSuratKeputusan->status_text }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Tanggal -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Tanggal</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Penetapan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->formatted_tanggal_penetapan }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Berlaku:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->formatted_tanggal_berlaku }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Ditandatangani Oleh:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->ditandatangani_oleh }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Dibuat Oleh:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->createdBy->name }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Dibuat Tanggal:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->created_at->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi File -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi File</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama File:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->file_name }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Ukuran File:</span>
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->formatted_file_size }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Aksi:</span>
                                            <div class="mt-2">
                                                <a href="{{ $viewSuratKeputusan->file_url }}" 
                                                   target="_blank"
                                                   class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    Download PDF
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deskripsi -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Deskripsi</h4>
                                    <div>
                                        @if($viewSuratKeputusan->deskripsi)
                                            <p class="text-sm text-gray-900">{{ $viewSuratKeputusan->deskripsi }}</p>
                                        @else
                                            <p class="text-sm text-gray-500 italic">Tidak ada deskripsi</p>
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
