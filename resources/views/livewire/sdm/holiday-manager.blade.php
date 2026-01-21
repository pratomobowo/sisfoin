<div class="space-y-6">
    <!-- Header -->
    <x-page-header 
        title="Kalender Hari Libur" 
        subtitle="Kelola hari libur nasional dan perusahaan"
        :breadcrumbs="['Biro SDM' => '#', 'Kalender Hari Libur' => route('sdm.absensi.holidays')]"
    >
        <x-slot name="actions">
            <x-button variant="success" wire:click="openModal()">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </x-slot>
                Tambah Hari Libur
            </x-button>
        </x-slot>
    </x-page-header>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" 
                       wire:model.live.debounce.300ms="search"
                       placeholder="Nama hari libur..."
                       class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select wire:model.live="filterYear"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @foreach(range(date('Y') - 2, date('Y') + 2) as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Holiday List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Berulang</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50">
                @forelse($holidays as $holiday)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ $holiday->date->isoFormat('dddd') }}</div>
                            <div class="text-sm text-gray-500">{{ $holiday->date->isoFormat('D MMMM Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $holiday->name }}</div>
                            @if($holiday->description)
                                <div class="text-xs text-gray-500">{{ Str::limit($holiday->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $typeColors = [
                                    'national' => 'bg-red-100 text-red-800',
                                    'company' => 'bg-blue-100 text-blue-800',
                                    'optional' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $typeColors[$holiday->type] ?? 'bg-gray-100' }}">
                                {{ $typeLabels[$holiday->type] ?? $holiday->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($holiday->is_recurring)
                                <span class="inline-flex items-center text-green-600">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Ya
                                </span>
                            @else
                                <span class="text-gray-400">Tidak</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <button wire:click="openModal({{ $holiday->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-sm mr-3">Edit</button>
                            <button wire:click="delete({{ $holiday->id }})" 
                                    wire:confirm="Yakin ingin menghapus hari libur ini?"
                                    class="text-red-600 hover:text-red-800 font-medium text-sm">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="font-medium">Belum ada hari libur</p>
                            <p class="text-sm">Klik tombol "Tambah Hari Libur" untuk menambahkan</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($holidays->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $holidays->links() }}
            </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">
                        {{ $editingId ? 'Edit Hari Libur' : 'Tambah Hari Libur' }}
                    </h3>
                    
                    <form wire:submit="save" class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal</label>
                            <input type="date" wire:model="date"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @error('date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Hari Libur</label>
                            <input type="text" wire:model="name" placeholder="Contoh: Hari Raya Idul Fitri"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipe</label>
                            <select wire:model="type"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="national">Nasional</option>
                                <option value="company">Perusahaan</option>
                                <option value="optional">Opsional</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="is_recurring" id="is_recurring"
                                   class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <label for="is_recurring" class="ml-2 text-sm text-gray-700">
                                Berulang setiap tahun (untuk tanggal tetap seperti Tahun Baru)
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Keterangan (Opsional)</label>
                            <textarea wire:model="description" rows="2" placeholder="Catatan tambahan..."
                                      class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal"
                                    class="px-5 py-2.5 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
