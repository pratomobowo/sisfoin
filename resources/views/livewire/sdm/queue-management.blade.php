<div class="flex items-center space-x-2">
    <!-- Status Indicator -->
    <div class="flex items-center space-x-2 px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm" 
         title="Queue Worker Status: {{ $isRunning ? 'Running' : 'Stopped' }}">
        <div class="flex-shrink-0">
            <span class="relative flex h-3 w-3">
                @if($isRunning)
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                @else
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                @endif
            </span>
        </div>
        <span class="text-xs font-medium text-gray-700 hidden lg:block">Queue: {{ $isRunning ? 'Running' : 'Stopped' }}</span>
        
        <button wire:click="checkStatus" 
                title="Refresh Status (Last: {{ $lastUpdate }})"
                class="ml-1 text-gray-400 hover:text-blue-500 transition-colors">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>
    </div>

    <!-- Control Button -->
    @if($isRunning)
        <button wire:click="stopQueue" 
                wire:loading.attr="disabled"
                onclick="return confirm('Hentikan queue worker sekarang? Pastikan tidak ada proses email penting yang sedang berjalan.')"
                title="Stop Queue Worker"
                class="inline-flex items-center px-4 py-2 border border-red-600 text-red-600 hover:bg-red-50 text-sm font-medium rounded-lg shadow-sm transition-colors">
            <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg wire:loading.remove class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
            </svg>
            Stop Worker
        </button>
    @else
        <button wire:click="startQueue" 
                wire:loading.attr="disabled"
                title="Start Queue Worker"
                class="inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 hover:bg-blue-50 text-sm font-medium rounded-lg shadow-sm transition-colors">
            <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg wire:loading.remove class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Start Worker
        </button>
    @endif
</div>
