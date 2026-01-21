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
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
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
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
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
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
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
    </div>

    <!-- Quick Reference -->
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl shadow-lg p-6 text-white">
        <h3 class="text-lg font-bold mb-4 flex items-center">
            <x-lucide-info class="w-5 h-5 mr-2 text-blue-400" />
            Referensi Status
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div class="bg-white/10 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <span class="w-3 h-3 rounded-full bg-green-400 mr-2"></span>
                    <span class="font-semibold">Datang Lebih Awal</span>
                </div>
                <p class="text-slate-400">Check-in sebelum batas waktu "early arrival"</p>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <span class="w-3 h-3 rounded-full bg-blue-400 mr-2"></span>
                    <span class="font-semibold">Tepat Waktu</span>
                </div>
                <p class="text-slate-400">Check-in antara batas awal dan toleransi terlambat</p>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <span class="w-3 h-3 rounded-full bg-yellow-400 mr-2"></span>
                    <span class="font-semibold">Terlambat</span>
                </div>
                <p class="text-slate-400">Check-in setelah jam masuk + toleransi</p>
            </div>
        </div>
    </div>
</div>
