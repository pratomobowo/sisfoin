<div class="space-y-6">
    <!-- Header -->
    <x-page-header 
        title="{{ $unitName }}" 
        subtitle="Kelola jadwal shift untuk unit ini"
        :breadcrumbs="[
            'Biro SDM' => '#', 
            'Kelola Shift Unit' => route('sdm.absensi.kelola-shift'),
            $unitName => route('sdm.absensi.unit-detail', ['unit' => $unitSlug])
        ]"
    >
        <x-slot name="actions">
            <div class="flex gap-2">
                <x-button variant="secondary" onclick="window.location='{{ route('sdm.absensi.unit-calendar', ['unit' => $unitSlug]) }}'">
                    <x-slot name="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </x-slot>
                    Kalendar View
                </x-button>
                <x-button variant="primary" wire:click="openModal()">
                    <x-slot name="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </x-slot>
                    Tambah Assignment
                </x-button>
            </div>
        </x-slot>
    </x-page-header>

    <!-- Employees with Assignments -->
    <div class="space-y-4">
        @forelse($this->employees as $employee)
            @php
                $employeeAssignments = $employee->id ? ($this->assignments[$employee->id] ?? collect()) : collect();
                $activeAssignment = $employeeAssignments->first(fn($a) => $a->status === 'active');
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden {{ !$employee->has_user ? 'border-yellow-200' : '' }}" wire:key="employee-{{ $employee->employee_id }}">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br {{ $employee->has_user ? 'from-blue-500 to-indigo-600' : 'from-gray-400 to-gray-500' }} flex items-center justify-center text-white font-bold text-lg mr-4">
                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $employee->name }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ $employee->nip }}
                                @if(!$employee->has_user)
                                    <span class="ml-2 px-2 py-0.5 text-[10px] font-medium bg-yellow-100 text-yellow-700 rounded">Belum ada akun</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if(!$employee->has_user)
                            <span class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg">-</span>
                        @elseif($activeAssignment)
                            <span class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-{{ $activeAssignment->workShift->color ?? 'blue' }}-100 text-{{ $activeAssignment->workShift->color ?? 'blue' }}-800">
                                {{ $activeAssignment->workShift->name ?? '-' }}
                            </span>
                        @else
                            <span class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-gray-100 rounded-lg">Default</span>
                        @endif
                        @if($employee->has_user)
                            <button wire:click="openModal({{ $employee->id }})" 
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
                
                @if($employee->has_user && $employeeAssignments->count() > 0)
                    <div class="divide-y divide-gray-50">
                        @foreach($employeeAssignments as $assignment)
                            <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 {{ $assignment->status !== 'active' ? 'opacity-60' : '' }}">
                                <div class="flex items-center gap-4">
                                    <span class="w-3 h-3 rounded-full bg-{{ $assignment->workShift->color ?? 'gray' }}-500"></span>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $assignment->workShift->name }}</span>
                                        <span class="text-gray-500 text-sm ml-2">
                                            ({{ substr($assignment->workShift->start_time, 0, 5) }}-{{ substr($assignment->workShift->end_time, 0, 5) }})
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm text-gray-600">
                                        {{ $assignment->start_date->isoFormat('D MMM Y') }}
                                        @if($assignment->end_date)
                                            → {{ $assignment->end_date->isoFormat('D MMM Y') }}
                                        @else
                                            → <span class="text-green-600 font-medium">Permanen</span>
                                        @endif
                                    </span>
                                    @if($assignment->status === 'active')
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase bg-green-100 text-green-700 rounded">Aktif</span>
                                    @elseif($assignment->status === 'upcoming')
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase bg-yellow-100 text-yellow-700 rounded">Mendatang</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase bg-gray-100 text-gray-500 rounded">Expired</span>
                                    @endif
                                    <div class="flex gap-1">
                                        <button wire:click="openModal(null, {{ $assignment->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button wire:click="delete({{ $assignment->id }})" wire:confirm="Yakin hapus assignment ini?" class="p-1.5 text-red-600 hover:bg-red-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($employee->has_user)
                    <div class="px-5 py-4 text-sm text-gray-500 italic">
                        Belum ada assignment - menggunakan shift default
                    </div>
                @else
                    <div class="px-5 py-4 text-sm text-yellow-600 italic bg-yellow-50">
                        Pegawai belum memiliki akun user - tidak bisa di-assign shift
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Belum Ada Pegawai Didaftarkan</h3>
                <p class="text-gray-500 mt-1 max-w-sm mx-auto">Klik tombol <strong>Tambah Assignment</strong> di atas untuk mendaftarkan pegawai dari unit ini ke jadwal shift khusus.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">
                        {{ $editingId ? 'Edit Assignment' : 'Tambah Assignment Baru' }}
                    </h3>
                    
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Pegawai</label>
                            <select wire:model="user_id" {{ $editingId ? 'disabled' : '' }}
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 {{ $editingId ? 'bg-gray-100' : '' }}">
                                <option value="">Pilih Pegawai...</option>
                                @foreach($this->allEmployeesInUnit as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->nip }})</option>
                                @endforeach
                            </select>
                            @error('user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Shift</label>
                            <select wire:model="work_shift_id"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Shift...</option>
                                @foreach($this->shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ substr($shift->start_time, 0, 5) }}-{{ substr($shift->end_time, 0, 5) }})</option>
                                @endforeach
                            </select>
                            @error('work_shift_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="date" wire:model="start_date"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                                <input type="date" wire:model="end_date" placeholder="Kosongkan jika permanen"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Kosongkan jika permanen</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan (Opsional)</label>
                            <textarea wire:model="notes" rows="2" placeholder="Catatan tambahan..."
                                      class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
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
