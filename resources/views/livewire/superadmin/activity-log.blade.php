<div class="space-y-6">
    <x-page-header 
        title="Log Aktivitas" 
        subtitle="Catatan aktivitas pengguna dalam sistem"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Superadmin' => '#', 'Log Aktivitas' => route('superadmin.activity-logs.index')]"
    >
        <x-slot name="actions">
            <x-button variant="primary" wire:click="exportLogs">
                <x-lucide-file-output class="h-4 w-4 mr-2" />
                Export Log
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
                           placeholder="Cari deskripsi, pengguna..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Log Type Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Log</label>
                <select wire:model.live="filterType"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Tipe</option>
                    @foreach($logNames as $name)
                        <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
                <input type="date" wire:model.live="dateFrom"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Date To -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai</label>
                <input type="date" wire:model.live="dateTo"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Module Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Modul</label>
                <select wire:model.live="moduleFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Modul</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Risk Filter -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Risiko</label>
                <select wire:model.live="riskLevelFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Risiko</option>
                    @foreach($riskLevels as $risk)
                        <option value="{{ $risk }}">{{ ucfirst($risk) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Result Filter -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasil</label>
                <select wire:model.live="resultFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Hasil</option>
                    @foreach($results as $result)
                        <option value="{{ $result }}">{{ ucfirst($result) }}</option>
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
                            Pengguna
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aktivitas
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Subjek
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($activities as $activity)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $activity->causer->name ?? 'System' }}</div>
                                    <div class="text-sm text-gray-500">{{ $activity->causer->email ?? 'system@app.com' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $activity->description }}</div>
                                @if($activity->log_name)
                                    <div class="text-sm text-gray-500">{{ ucfirst($activity->log_name) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($activity->subject)
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ class_basename($activity->subject_type) }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        ID: {{ $activity->subject_id }}
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    {{ $activity->created_at->format('d M Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $activity->created_at->format('H:i:s') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @if($activity->properties && (is_array($activity->properties) ? count($activity->properties) > 0 : $activity->properties->count() > 0))
                                        <button type="button"
                                                onclick="openPropertiesModal({{ $activity->id }}, {{ json_encode($activity->properties) }})"
                                                title="Lihat Properties" aria-label="Lihat Properties"
                                                class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-blue-600 transition-colors">
                                            <x-lucide-info class="w-5 h-5" />
                                        </button>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <x-lucide-clipboard-list class="w-12 h-12 text-gray-400 mb-4" />
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada log aktivitas ditemukan</h3>
                                    <p class="text-sm text-gray-500">Coba ubah filter pencarian atau periode tanggal</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($activities->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-white">
                <x-superadmin.pagination 
                    :currentPage="$activities->currentPage()"
                    :lastPage="$activities->lastPage()"
                    :total="$activities->total()"
                    :perPage="$activities->perPage()"
                    :perPageOptions="[10, 25, 50, 100]"
                    :showPageInfo="true"
                    :showPerPage="false"
                    alignment="justify-between"
                    perPageWireModel="perPage"
                    previousPageWireModel="previousPage"
                    nextPageWireModel="nextPage"
                    gotoPageWireModel="gotoPage"
                />
            </div>
        @endif
    </div>

    <!-- Properties Modal -->
    <div id="propertiesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Properties Aktivitas</h3>
                    <button type="button" onclick="closePropertiesModal()" class="text-gray-400 hover:text-gray-600">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="mt-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre id="propertiesContent" class="text-sm text-gray-800 whitespace-pre-wrap overflow-auto max-h-96"></pre>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex justify-end pt-4 border-t mt-4">
                    <button type="button" onclick="closePropertiesModal()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openPropertiesModal(activityId, properties) {
        document.getElementById('propertiesContent').textContent = JSON.stringify(properties, null, 2);
        document.getElementById('propertiesModal').classList.remove('hidden');
    }

    function closePropertiesModal() {
        document.getElementById('propertiesModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('propertiesModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePropertiesModal();
        }
    });
</script>

@push('styles')
<style>
    /* Custom styles for activity log */
    .activity-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto refresh every 5 minutes for activity log updates
        setInterval(function() {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('refreshData');
            }
        }, 300000); // 5 minutes
        
        // Enhanced modal animations
        document.addEventListener('livewire:init', function() {
            Livewire.on('modalOpened', function() {
                const modal = document.querySelector('#propertiesModal');
                if (modal && !modal.classList.contains('hidden')) {
                    modal.querySelector('.relative').classList.add('modal-enter');
                }
            });
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape to close modals
            if (e.key === 'Escape') {
                const modal = document.getElementById('propertiesModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closePropertiesModal();
                }
            }
            
            // Ctrl/Cmd + E for export
            if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                e.preventDefault();
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('exportLogs');
                }
            }
        });
    });
</script>
@endpush
