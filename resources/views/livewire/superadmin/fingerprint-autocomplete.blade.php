<!-- Fingerprint Autocomplete Component -->
<div class="relative">
    <input type="text" 
           wire:model.live="fingerprintSearch"
           wire:keyup.debounce.300ms="filterFingerprintUsers"
           wire:click.outside="hideFingerprintAutocomplete"
           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
           placeholder="Ketik untuk cari user fingerprint..."
           maxlength="10">
    
    <!-- Autocomplete Dropdown -->
    @if($showFingerprintAutocomplete)
        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
            <div class="p-2 bg-gray-50 border-b border-gray-200">
                <p class="text-xs text-gray-600">User dari database fingerprint:</p>
            </div>
            
            @if($isLoadingFingerprintUsers)
                <div class="px-3 py-4 text-center">
                    <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-gray-600">Memuat data...</p>
                </div>
            @elseif(!empty($this->fingerprintUsers))
                @foreach($this->fingerprintUsers as $fingerprintUser)
                    <div wire:click="selectFingerprintUser({{ $fingerprintUser['pin'] }}, '{{ $fingerprintUser['name'] ?? 'Unknown' }}')"
                         class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $fingerprintUser['name'] ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">PIN: {{ $fingerprintUser['pin'] }}</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="px-3 py-4 text-center text-gray-500">
                    <p class="text-sm">Tidak ada user yang cocok</p>
                </div>
            @endif
        </div>
    @endif
</div>
