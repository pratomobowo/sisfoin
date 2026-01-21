@props(['padding' => true, 'shadow' => 'sm'])

@php
$shadowClasses = [
    'none' => '',
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
    'xl' => 'shadow-xl'
];

$paddingClass = $padding ? 'p-6' : '';
$shadowClass = $shadowClasses[$shadow] ?? $shadowClasses['sm'];
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-xl {$shadowClass} {$paddingClass}"]) }}>
    @isset($header)
        <div class="border-b border-gray-200 pb-4 mb-4">
            {{ $header }}
        </div>
    @endisset
    
    <div class="{{ isset($header) ? '' : '' }}">
        {{ $slot }}
    </div>
    
    @isset($footer)
        <div class="border-t border-gray-200 pt-4 mt-4">
            {{ $footer }}
        </div>
    @endisset
</div>