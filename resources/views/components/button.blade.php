@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'disabled' => false
])

@php
    $baseClasses = 'inline-flex items-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';
    
    $variants = [
        'primary' => 'text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 border border-transparent',
        'secondary' => 'text-gray-700 bg-white hover:bg-gray-50 focus:ring-blue-500 border border-gray-300',
        'success' => 'text-white bg-green-600 hover:bg-green-700 focus:ring-green-500 border border-transparent',
        'danger' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500 border border-transparent',
        'warning' => 'text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500 border border-transparent',
        'info' => 'text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 border border-transparent',
        'ghost' => 'text-gray-700 bg-transparent hover:bg-gray-100 focus:ring-gray-500 border border-transparent'
    ];
    
    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs',
        'sm' => 'px-3 py-2 text-sm leading-4',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-4 py-2 text-base',
        'xl' => 'px-6 py-3 text-base'
    ];
    
    $iconSizes = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        'xl' => 'w-5 h-5'
    ];
    
    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
    
    if ($disabled || $loading) {
        $classes .= ' opacity-50 cursor-not-allowed';
    }
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <svg class="{{ $iconSizes[$size] }} {{ $slot->isEmpty() ? '' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
        
        @if($loading)
            <svg class="animate-spin {{ $iconSizes[$size] }} {{ $slot->isEmpty() ? '' : 'mr-2' }}" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <svg class="{{ $iconSizes[$size] }} {{ $slot->isEmpty() ? '' : 'ml-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @if($disabled || $loading) disabled @endif>
        @if($icon && $iconPosition === 'left')
            <svg class="{{ $iconSizes[$size] }} {{ $slot->isEmpty() ? '' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
        
        @if($loading)
            <svg class="animate-spin {{ $iconSizes[$size] }} {{ $slot->isEmpty() ? '' : 'mr-2' }}" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <svg class="{{ $iconSizes[$size] }} {{ $slot->isEmpty() ? '' : 'ml-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
    </button>
@endif