<div class="space-y-6">
    <!-- Header -->
    <x-page-header 
        title="Pengaturan Absensi" 
        subtitle="Konfigurasi aturan absensi, jam kerja, dan toleransi keterlambatan"
        :breadcrumbs="['Biro SDM' => '#', 'Pengaturan Absensi' => route('sdm.absensi.settings')]"
    >
        <x-slot name="actions">
            <x-button variant="primary" wire:click="save">
                <x-lucide-check class="w-4 h-4 mr-2" />
                Simpan Pengaturan
            </x-button>
        </x-slot>
    </x-page-header>

    <!-- Settings Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Schedule Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900 flex items-center">
                    <x-lucide-clock class="w-5 h-5 mr-2 text-blue-600" />
                    Jadwal Kerja
                </h3>
            </div>
            <div class="p-6 space-y-5">
                @foreach($groupedSettings->get('schedule', collect()) as $setting)
                    @if($setting->key !== 'working_days')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $setting->label }}</label>
                            @if($setting->type === 'time')
                                <input type="time" 
                                       wire:model="settings.{{ $setting->key }}"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            @elseif($setting->type === 'integer')
                                <input type="number" 
                                       wire:model="settings.{{ $setting->key }}"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            @else
                                <input type="text" 
                                       wire:model="settings.{{ $setting->key }}"
                                       class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            @endif
                            @if($setting->description)
                                <p class="mt-1 text-xs text-gray-500">{{ $setting->description }}</p>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Rules Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900 flex items-center">
                    <x-lucide-shield-check class="w-5 h-5 mr-2 text-purple-600" />
                    Aturan Absensi
                </h3>
            </div>
            <div class="p-6 space-y-5">
                @foreach($groupedSettings->get('rules', collect()) as $setting)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $setting->label }}</label>
                        @if($setting->type === 'integer')
                            <input type="number" 
                                   wire:model="settings.{{ $setting->key }}"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                        @else
                            <input type="text" 
                                   wire:model="settings.{{ $setting->key }}"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                        @endif
                        @if($setting->description)
                            <p class="mt-1 text-xs text-gray-500">{{ $setting->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Working Days -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900 flex items-center">
                    <x-lucide-calendar class="w-5 h-5 mr-2 text-green-600" />
                    Hari Kerja
                </h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-500 mb-4">Pilih hari-hari yang dihitung sebagai hari kerja</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($this->dayLabels as $dayNum => $dayName)
                        <label class="relative cursor-pointer">
                            <input type="checkbox" 
                                   wire:model="workingDays" 
                                   value="{{ $dayNum }}"
                                   class="peer sr-only">
                            <div class="px-5 py-3 rounded-xl border-2 font-medium text-sm transition-all duration-200
                                        peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700
                                        border-gray-200 text-gray-600 hover:border-gray-300">
                                {{ $dayName }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Holiday Management -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900 flex items-center">
                    <x-lucide-calendar-days class="w-5 h-5 mr-2 text-amber-600" />
                    Tanggal Libur SDM
                </h3>
                <p class="text-xs text-gray-500 mt-1">Hari libur tidak dihitung sebagai tidak hadir pada monitor/rekap absensi.</p>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal</label>
                        <input type="date" wire:model="holidayDate" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        @error('holidayDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Libur</label>
                        <input type="text" wire:model="holidayName" placeholder="Contoh: Isra Miraj" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        @error('holidayName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jenis</label>
                        <select wire:model="holidayType" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                            <option value="national">Nasional</option>
                            <option value="company">Internal</option>
                            <option value="optional">Opsional</option>
                        </select>
                        @error('holidayType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Keterangan</label>
                        <input type="text" wire:model="holidayDescription" placeholder="Opsional" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        @error('holidayDescription') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col justify-end gap-2">
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input type="checkbox" wire:model="holidayIsRecurring" class="mr-2 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            Ulang tiap tahun
                        </label>
                        <div class="flex gap-2">
                            <button type="button" wire:click="saveHoliday" class="flex-1 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold">
                                {{ $holidayId ? 'Update' : 'Tambah' }}
                            </button>
                            @if($holidayId)
                                <button type="button" wire:click="resetHolidayForm" class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold">Batal</button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jenis</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sifat</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($holidays as $holiday)
                                    <tr class="hover:bg-amber-50/40">
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ \Carbon\Carbon::parse($holiday['date'])->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $holiday['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($holiday['type']) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $holiday['is_recurring'] ? 'Tahunan' : 'Sekali' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <button type="button" wire:click="editHoliday({{ $holiday['id'] }})" class="px-2.5 py-1 text-xs rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50">Edit</button>
                                                <button type="button" wire:click="deleteHoliday({{ $holiday['id'] }})" onclick="confirm('Hapus tanggal libur ini?') || event.stopImmediatePropagation()" class="px-2.5 py-1 text-xs rounded-md border border-red-200 text-red-700 hover:bg-red-50">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada tanggal libur. Tambahkan agar hari libur tidak dianggap tidak hadir.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($holidays->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 bg-white">
                            <x-superadmin.pagination
                                :currentPage="$holidays->currentPage()"
                                :lastPage="$holidays->lastPage()"
                                :total="$holidays->total()"
                                :perPage="$holidays->perPage()"
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
            </div>
        </div>
    </div>

    <!-- Quick Reference -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
            <x-lucide-info class="w-5 h-5 mr-2 text-blue-500" />
            Referensi Status
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div class="bg-green-50 border border-green-100 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <span class="w-3 h-3 rounded-full bg-green-400 mr-2"></span>
                    <span class="font-semibold text-gray-900">Datang Lebih Awal</span>
                </div>
                <p class="text-gray-600">Check-in sebelum batas waktu "early arrival"</p>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <span class="w-3 h-3 rounded-full bg-blue-400 mr-2"></span>
                    <span class="font-semibold text-gray-900">Tepat Waktu</span>
                </div>
                <p class="text-gray-600">Check-in antara batas awal dan toleransi terlambat</p>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <span class="w-3 h-3 rounded-full bg-yellow-400 mr-2"></span>
                    <span class="font-semibold text-gray-900">Terlambat</span>
                </div>
                <p class="text-gray-600">Check-in setelah jam masuk + toleransi</p>
            </div>
        </div>
    </div>
</div>
