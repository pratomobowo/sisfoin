@props([
    'title' => '',
    'description' => '',
    'showBackButton' => false,
    'backRoute' => null,
    'backText' => 'Kembali',
    'actions' => null,
])

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $title }}</h2>
            @if($description)
                <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
            @endif
        </div>
        
        <div class="mt-4 sm:mt-0 flex space-x-2">
            @if($showBackButton && $backRoute)
                <a href="{{ $backRoute }}" 
                   class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>{{ $backText }}</span>
                </a>
            @endif
            
            @if($actions)
                {!! $actions !!}
            @endif
        </div>
    </div>
</div>
