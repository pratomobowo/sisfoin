@props([
    'id' => 'confirmation-modal',              // Unique ID for modal instance
    'title' => 'Konfirmasi Hapus',            // Modal title
    'message' => 'Apakah Anda yakin?',         // Confirmation message
    'confirmText' => 'Hapus',                   // Confirm button text
    'cancelText' => 'Batal',                   // Cancel button text
    'type' => 'danger',                         // Modal type: danger, warning, info
    'confirmAction' => null,                    // Livewire action name
    'confirmParams' => [],                      // Parameters for Livewire action
    'show' => false,                           // Initial show state
    'maxWidth' => 'sm',                         // Modal size: sm, md, lg, xl, 2xl
    'icon' => null,                            // Custom icon (optional)
])

@php
// Map modal types to colors and icons
$typeConfig = [
    'danger' => [
        'bgColor' => 'bg-red-100',
        'textColor' => 'text-red-600',
        'buttonBg' => 'bg-red-600 hover:bg-red-700',
        'buttonBorder' => 'border-red-600',
        'icon' => 'exclamation-triangle',
    ],
    'warning' => [
        'bgColor' => 'bg-yellow-100',
        'textColor' => 'text-yellow-600',
        'buttonBg' => 'bg-yellow-600 hover:bg-yellow-700',
        'buttonBorder' => 'border-yellow-600',
        'icon' => 'exclamation-circle',
    ],
    'info' => [
        'bgColor' => 'bg-blue-100',
        'textColor' => 'text-blue-600',
        'buttonBg' => 'bg-blue-600 hover:bg-blue-700',
        'buttonBorder' => 'border-blue-600',
        'icon' => 'information-circle',
    ],
];

// Get config for current type or default to danger
$config = $typeConfig[$type] ?? $typeConfig['danger'];

// Map max width to actual classes
$maxWidthClasses = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
];

$maxWidthClass = $maxWidthClasses[$maxWidth] ?? $maxWidthClasses['sm'];

// Use custom icon if provided, otherwise use type-based icon
$iconName = $icon ?? $config['icon'];
@endphp

<x-modal 
    :name="$id" 
    :show="$show"
    :maxWidth="$maxWidth"
    focusable
    x-bind:id="$id"
>
    <div class="p-6">
        <!-- Icon -->
        <div class="flex items-center justify-center w-12 h-12 mx-auto {{ $config['bgColor'] }} rounded-full mb-4">
            <svg class="w-6 h-6 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($iconName === 'exclamation-triangle')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                @elseif($iconName === 'exclamation-circle')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                @elseif($iconName === 'information-circle')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                @else
                    <!-- Default icon -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                @endif
            </svg>
        </div>

        <!-- Title -->
        <h3 class="text-lg font-medium text-gray-900 text-center mb-2">{{ $title }}</h3>
        
        <!-- Message -->
        <p class="text-sm text-gray-500 text-center mb-6">
            {!! $message !!}
        </p>

        <!-- Buttons -->
        <div class="flex space-x-3">
            <!-- Cancel Button -->
            <button 
                x-on:click="$dispatch('close-modal', '{{ $id }}')"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                {{ $cancelText }}
            </button>
            
            <!-- Confirm Button -->
            @if($confirmAction)
                @if(!empty($confirmParams))
                    <button 
                        wire:click="{{ $confirmAction }}({{ json_encode($confirmParams) }})"
                        class="flex-1 px-4 py-2 {{ $config['buttonBg'] }} border border-transparent rounded-md text-sm font-medium text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $config['textColor'] }}"
                    >
                        {{ $confirmText }}
                    </button>
                @else
                    <button 
                        wire:click="{{ $confirmAction }}"
                        class="flex-1 px-4 py-2 {{ $config['buttonBg'] }} border border-transparent rounded-md text-sm font-medium text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $config['textColor'] }}"
                    >
                        {{ $confirmText }}
                    </button>
                @endif
            @else
                <button 
                    class="flex-1 px-4 py-2 {{ $config['buttonBg'] }} border border-transparent rounded-md text-sm font-medium text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $config['textColor'] }}"
                >
                    {{ $confirmText }}
                </button>
            @endif
        </div>
    </div>
</x-modal>
