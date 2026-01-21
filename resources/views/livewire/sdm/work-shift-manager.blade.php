<div class="space-y-6">
    <!-- Header -->
    <x-page-header 
        title="Shift Kerja" 
        subtitle="Kelola definisi shift dan jadwal kerja"
        :breadcrumbs="['Biro SDM' => '#', 'Definisi Shift' => route('sdm.absensi.shifts')]"
    >
        <x-slot name="actions">
            <x-button variant="primary" wire:click="openModal()">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </x-slot>
                Tambah Shift
            </x-button>
        </x-slot>
    </x-page-header>

    <!-- Shifts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($shifts as $shift)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow {{ !$shift->is_active ? 'opacity-60' : '' }}">
                <div class="h-2 bg-{{ $shift->color }}-500"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $shift->name }}</h3>
                            <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $shift->code }}</span>
                        </div>
                        @if($shift->is_default)
                            <span class="px-2 py-1 text-[10px] font-bold uppercase bg-green-100 text-green-800 rounded-full">Default</span>
                        @endif
                    </div>
                    
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>Early: {{ substr($shift->early_arrival_threshold, 0, 5) }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Toleransi: {{ $shift->late_tolerance_minutes }} menit</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $shift->work_hours }} jam kerja</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="flex gap-2">
                            <button wire:click="openModal({{ $shift->id }})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</button>
                            @if(!$shift->is_default)
                                <button wire:click="delete({{ $shift->id }})" wire:confirm="Yakin ingin menghapus shift ini?" class="text-red-600 hover:text-red-800 text-sm font-medium">Hapus</button>
                            @endif
                        </div>
                        @if(!$shift->is_default)
                            <button wire:click="setAsDefault({{ $shift->id }})" class="text-xs text-gray-500 hover:text-gray-700">Set Default</button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">
                        {{ $editingId ? 'Edit Shift' : 'Tambah Shift Baru' }}
                    </h3>
                    
                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Shift</label>
                                <input type="text" wire:model="name" placeholder="Shift Pagi"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Kode</label>
                                <input type="text" wire:model="code" placeholder="PAGI" maxlength="20"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Masuk</label>
                                <input type="time" wire:model="start_time"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Pulang</label>
                                <input type="time" wire:model="end_time"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Batas Early Arrival</label>
                                <input type="time" wire:model="early_arrival_threshold"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Toleransi Terlambat (menit)</label>
                                <input type="number" wire:model="late_tolerance_minutes" min="0"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Kerja</label>
                                <input type="number" wire:model="work_hours" min="0" step="0.5"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Warna</label>
                                <select wire:model="color"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    @foreach($colors as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi (Opsional)</label>
                            <textarea wire:model="description" rows="2" placeholder="Keterangan shift..."
                                      class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="is_active" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Aktif</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="is_default" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Set sebagai Default</span>
                            </label>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal"
                                    class="px-5 py-2.5 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
