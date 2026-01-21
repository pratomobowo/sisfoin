@props(['type' => 'button', 'size' => 'md'])

@php
$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
    'xl' => 'px-8 py-4 text-lg'
];

$classes = $sizeClasses[$size];
@endphp

<button 
    type=\"{{ $type }}\"
    {{ $attributes->merge(['class' => \"inline-flex items-center justify-center rounded-xl font-medium border border-gray-300 bg-white text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed {$classes}\"]) }}
>
    {{ $slot }}
</button>