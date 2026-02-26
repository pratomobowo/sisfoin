<div class="space-y-6">
    <x-page-header 
        title="Manajemen Peran" 
        subtitle="Kelola hak akses dan peran pengguna"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Superadmin' => '#', 'Peran' => route('superadmin.roles.index')]"
    >
        <x-slot name="actions">
            <x-button variant="primary" href="{{ route('superadmin.roles.create') }}">
                <x-lucide-plus class="w-4 h-4 mr-2" />
                Tambah Peran
            </x-button>
        </x-slot>
    </x-page-header>


    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Search -->
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-lucide-search class="w-4 h-4 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           placeholder="Cari nama peran..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- User Count Filter -->
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pengguna</label>
                <select wire:model.live="filterUserCount"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    <option value="has_users">Memiliki Pengguna</option>
                    <option value="no_users">Tidak Ada Pengguna</option>
                </select>
            </div>

            <!-- Module Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Akses Modul</label>
                <select wire:model.live="filterModule"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Modul</option>
                    @foreach($availableModules as $moduleKey => $moduleData)
                        <option value="{{ $moduleKey }}">{{ $moduleData['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Filter Button -->
            <div class="md:col-span-1 flex items-end">
                <button wire:click="resetFilters"
                        class="w-10 h-10 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors flex items-center justify-center"
                        title="Reset Filter">
                    <x-lucide-rotate-ccw class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>


    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Peran
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pengguna
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Modul Akses
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $role->name }}</div>
                                    @if($role->description)
                                        <div class="text-xs text-gray-400 mt-1">{{ $role->description }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $role->users_count ?? $role->users->count() }} pengguna
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $rolePermissions = $role->permissions->pluck('name')->toArray();
                                        $roleModules = [];
                                        foreach($availableModules as $moduleKey => $moduleData) {
                                            if (isset($moduleData['permissions'])) {
                                                $modulePermissions = array_keys($moduleData['permissions']);
                                                if (array_intersect($modulePermissions, $rolePermissions)) {
                                                    $roleModules[] = $moduleData['label'];
                                                }
                                            }
                                        }
                                    @endphp
                                    @if(count($roleModules) > 0)
                                        @foreach(array_slice($roleModules, 0, 3) as $module)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $module }}
                                            </span>
                                        @endforeach
                                        @if(count($roleModules) > 3)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                                +{{ count($roleModules) - 3 }} lainnya
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400">Tidak ada modul</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @if($role->name !== 'super-admin')
                                        <!-- Edit Button -->
                                        <a href="{{ route('superadmin.roles.edit', $role->id) }}"
                                           title="Edit Peran" aria-label="Edit Peran"
                                           class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-green-600">
                                            <x-lucide-edit class="w-5 h-5" />
                                        </a>
                                        
                                        <!-- Delete Button -->
                                        <button wire:click="confirmDelete({{ $role->id }})"
                                                title="Hapus Peran" aria-label="Hapus Peran"
                                                class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-red-600">
                                            <x-lucide-trash-2 class="w-5 h-5" />
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-sm">Tidak dapat diubah</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <x-lucide-shield-off class="w-12 h-12 text-gray-400 mb-4" />
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada peran ditemukan</h3>
                                    <p class="text-sm text-gray-500">Mulai dengan membuat peran baru untuk sistem Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($roles->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-white">
                <x-superadmin.pagination 
                    :currentPage="$roles->currentPage()"
                    :lastPage="$roles->lastPage()"
                    :total="$roles->total()"
                    :perPage="$roles->perPage()"
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

    <!-- Delete Confirmation Modal - Reusable Component -->
    <x-superadmin.confirmation-modal 
        id="deleteRoleModal"
        title="Hapus Peran"
        message="Apakah Anda yakin ingin menghapus peran <strong>{{ $roleToDelete->display_name ?? $roleToDelete->name ?? '' }}</strong>?"
        type="danger"
        confirmText="Hapus"
        cancelText="Batal"
        :show="$showDeleteModal"
        confirmAction="delete"
        maxWidth="sm"
    />

    <!-- Bulk Permissions Assignment Modal -->
    @if($showBulkPermissionModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Berikan Hak Akses</h3>
                    <button wire:click="$set('showBulkPermissionModal', false)" 
                            class="text-gray-400 hover:text-gray-600 p-1 rounded-md hover:bg-gray-100">
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">
                            Pilih modul untuk diberikan hak akses ke <strong>{{ count($selectedRoles) }}</strong> peran yang dipilih:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($selectedRoles as $roleId)
                                @php
                                    $role = \Spatie\Permission\Models\Role::find($roleId);
                                @endphp
                                @if($role)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $role->display_name ?? $role->name }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Modul</label>
                        <select wire:model.live="bulkPermissionModule"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Modul --</option>
                            @foreach($availableModules as $moduleKey => $moduleData)
                                <option value="{{ $moduleKey }}">{{ $moduleData['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($bulkPermissionModule)
                        <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Hak Akses yang akan diberikan:</h4>
                            @if(isset($availableModules[$bulkPermissionModule]['permissions']))
                                <div class="space-y-1">
                                    @foreach($availableModules[$bulkPermissionModule]['permissions'] as $permKey => $permData)
                                        <div class="flex items-center text-sm text-blue-800">
                                            <x-lucide-check class="w-4 h-4 mr-2 text-blue-600" />
                                            {{ $permData['label'] ?? $permKey }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end p-4 border-t border-gray-200 space-x-3">
                    <button wire:click="$set('showBulkPermissionModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button wire:click="bulkAssignPermissions"
                            wire:loading.attr="disabled"
                            :disabled="!$bulkPermissionModule"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors">
                        <span wire:loading.remove>Berikan Hak Akses</span>
                        <span wire:loading>Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    /* Custom styles for role management */
    .role-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .status-badge {
        transition: all 0.2s ease-in-out;
    }
    
    .status-badge:hover {
        transform: scale(1.05);
    }
    
    /* Loading animation */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    /* Modal animations */
    .modal-enter {
        animation: modalEnter 0.3s ease-out;
    }
    
    @keyframes modalEnter {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    /* Table hover effects */
    .table-row-hover {
        transition: all 0.2s ease-in-out;
    }
    
    .table-row-hover:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    /* Button hover effects */
    .btn-hover-effect {
        transition: all 0.2s ease-in-out;
        position: relative;
        overflow: hidden;
    }
    
    .btn-hover-effect:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto refresh every 5 minutes for status updates
        setInterval(function() {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('refreshData');
            }
        }, 300000); // 5 minutes
        
        // Enhanced modal animations
        document.addEventListener('livewire:init', function() {
            Livewire.on('modalOpened', function() {
                const modal = document.querySelector('.fixed.inset-0');
                if (modal) {
                    modal.querySelector('.inline-block').classList.add('modal-enter');
                }
            });
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + N for new role
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('create');
                }
            }
            
            // Escape to close modals
            if (e.key === 'Escape') {
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('closeModal');
                }
            }
        });
    });
</script>
@endpush
