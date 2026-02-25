<div>
    <x-page-header title="Manajemen Pengguna" subtitle="Kelola pengguna sistem dan hak akses mereka">
        <x-slot name="actions">
            @can('users.create')
                <button wire:click="showImportUsers" 
                        class="btn-secondary flex items-center space-x-2">
                    <x-lucide-upload-cloud class="w-4 h-4" />
                    <span>Import Pengguna</span>
                </button>
                <a href="{{ route('superadmin.users.create') }}" 
                   class="btn-primary flex items-center space-x-2">
                    <x-lucide-plus class="w-4 h-4" />
                    <span>Tambah Pengguna</span>
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="space-y-6 mt-6">

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Search -->
                <div class="md:col-span-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-lucide-search class="w-4 h-4 text-gray-400" />
                        </div>
                        <input wire:model.live.debounce.300ms="search"
                               type="text"
                               placeholder="Cari nama, email, atau NIP..."
                               class="form-input w-full pl-10 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Role Filter -->
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter Peran</label>
                    <select wire:model.live="selectedRole"
                            class="form-select w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Peran</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fingerprint Status Filter -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter Sidik Jari</label>
                    <select wire:model.live="selectedFingerprintStatus"
                            class="form-select w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua</option>
                        <option value="active">Aktif</option>
                        <option value="no_pin">PIN Kosong</option>
                        <option value="disabled">Tidak Aktif</option>
                    </select>
                </div>

                <!-- Reset Filter Button -->
                <div class="md:col-span-1 flex items-end">
                    <button wire:click="resetFilters"
                            class="w-full h-[42px] bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors flex items-center justify-center"
                            title="Reset Filter">
                        <x-lucide-refresh-cw class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Pengguna
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Peran
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Sidik Jari
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Bergabung
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        @if($user->nip)
                                            <div class="text-xs text-gray-400 mt-0.5">NIP: {{ $user->nip }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $role->name === 'super-admin' ? 'bg-red-100 text-red-800' : ($role->name === 'admin' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ ucfirst($role->name) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->fingerprint_enabled)
                                        @if($user->fingerprint_pin)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <x-lucide-check-circle class="w-3 h-3 mr-1" />
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <x-lucide-alert-triangle class="w-3 h-3 mr-1" />
                                                PIN Kosong
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <x-lucide-x-circle class="w-3 h-3 mr-1" />
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        {{ $user->created_at->format('d M Y') }}
                                        <div class="text-xs text-gray-400">{{ $user->created_at->format('H:i') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        @can('users.update')
                                            @if($user->id !== auth()->id())
                                                <a href="{{ route('superadmin.users.edit', $user->id) }}" 
                                                   title="Edit Pengguna"
                                                   class="p-2 rounded-lg hover:bg-blue-50 text-blue-600 transition-colors">
                                                    <x-lucide-pencil class="w-4 h-4" />
                                                </a>
                                            @endif
                                        @endcan
                                        
                                        @can('users.delete')
                                            @if($user->id !== auth()->id())
                                                <button wire:click="confirmDelete({{ $user->id }})" 
                                                        title="Hapus Pengguna"
                                                        class="p-2 rounded-lg hover:bg-red-50 text-red-600 transition-colors">
                                                    <x-lucide-trash-2 class="w-4 h-4" />
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-100 p-4 rounded-full mb-4">
                                            <x-lucide-user class="w-8 h-8 text-gray-400" />
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">Tidak ada pengguna ditemukan</h3>
                                        <p class="text-sm text-gray-500">Coba ubah filter pencarian atau tambah pengguna baru</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-white">
                    <x-superadmin.pagination
                        :currentPage="$users->currentPage()"
                        :lastPage="$users->lastPage()"
                        :total="$users->total()"
                        :perPage="$users->perPage()"
                        :perPageOptions="[10, 25, 50, 100]"
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
        
        <!-- Delete Confirmation Modal -->
        <div x-data="{ show: @entangle('showDeleteModal') }"
             x-show="show"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
             
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="show = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    
                    <div class="bg-white px-4 py-3 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Hapus Pengguna</h3>
                        <button wire:click="closeDeleteModal" class="text-gray-400 hover:text-gray-500">
                            <x-lucide-x class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-lucide-alert-triangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Konfirmasi Penghapusan
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Apakah Anda yakin ingin menghapus pengguna 
                                        <span class="font-bold text-gray-900">{{ $userToDelete->name ?? '' }}</span>?
                                        Tindakan ini tidak dapat dibatalkan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="delete" type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading.remove wire:target="delete">Hapus</span>
                            <span wire:loading wire:target="delete" class="flex items-center">
                                <x-lucide-loader-2 class="animate-spin -ml-1 mr-2 h-4 w-4" />
                                Menghapus...
                            </span>
                        </button>
                        <button wire:click="closeDeleteModal" type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Import Modal Component -->
    <livewire:superadmin.user-import-modal />

</div>
