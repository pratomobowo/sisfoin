<div class="space-y-4">
    <!-- Basic Information -->
    <div class="space-y-3">
        <h4 class="text-base font-medium text-gray-900">Informasi Dasar <span class="text-red-500">*</span></h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Peran <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       wire:model.live="name" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="contoh: admin-keuangan">
                @error('name') 
                    <p class="text-red-600 text-sm mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Tampilan <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       wire:model.live="display_name" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="contoh: Admin Keuangan">
                @error('display_name') 
                    <p class="text-red-600 text-sm mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Deskripsi
            </label>
            <textarea wire:model.live="description" 
                      rows="2"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Deskripsi singkat tentang peran ini"></textarea>
            @error('description') 
                <p class="text-red-600 text-sm mt-1 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-200 pt-4"></div>

    <!-- Permission Selection -->
    <div class="space-y-3">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h4 class="text-base font-medium text-gray-900">Hak Akses Modul <span class="text-red-500">*</span></h4>
                <p class="text-sm text-gray-500 mt-1">Pilih permission spesifik yang dibutuhkan role ini. Menu akan mengikuti permission yang dipilih.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                {{ count($selectedPermissions) }} izin dipilih
            </span>
        </div>

        <div class="space-y-3 max-h-[28rem] overflow-y-auto pr-1">
            @foreach($availableModules as $moduleKey => $moduleData)
                @php
                    $counts = $this->getModuleSelectionCount($moduleKey);
                    $isFull = $this->isModuleFullySelected($moduleKey);
                    $isPartial = $counts['selected'] > 0 && ! $isFull;
                @endphp
                <div class="rounded-xl border {{ $isFull ? 'border-blue-200 bg-blue-50/50' : ($isPartial ? 'border-amber-200 bg-amber-50/40' : 'border-gray-200 bg-white') }} p-4 transition-colors">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $moduleData['label'] }}</div>
                            <div class="mt-1 text-xs text-gray-500">
                                {{ $counts['selected'] }}/{{ $counts['total'] }} izin dipilih
                                @if($isFull)
                                    <span class="ml-2 font-medium text-blue-700">Akses penuh</span>
                                @elseif($isPartial)
                                    <span class="ml-2 font-medium text-amber-700">Sebagian akses</span>
                                @endif
                            </div>
                        </div>
                        <button type="button"
                                wire:click="toggleModulePermissions('{{ $moduleKey }}')"
                                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            {{ $isFull ? 'Kosongkan Modul' : 'Pilih Semua' }}
                        </button>
                    </div>

                    @if(isset($moduleData['permissions']))
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($moduleData['permissions'] as $permissionName => $permissionLabel)
                                <label class="flex items-start gap-2 rounded-lg border border-gray-100 bg-white/80 p-2 text-sm hover:border-blue-200 hover:bg-blue-50/40 cursor-pointer">
                                    <input type="checkbox"
                                           wire:model.live="selectedPermissions"
                                           value="{{ $permissionName }}"
                                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span>
                                        <span class="block font-medium text-gray-800">{{ is_array($permissionLabel) ? ($permissionLabel['label'] ?? $permissionName) : $permissionLabel }}</span>
                                        <span class="block text-[11px] text-gray-400">{{ $permissionName }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @error('selectedPermissions')
            <p class="text-red-600 text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $message }}
            </p>
        @enderror
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-200 pt-4"></div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('superadmin.roles.index') }}" 
           class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
            Batal
        </a>
        <button wire:click="save"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove>
                {{ $roleId ? 'Update Peran' : 'Simpan Peran' }}
            </span>
            <span wire:loading>
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            </span>
        </button>
    </div>
</div>
