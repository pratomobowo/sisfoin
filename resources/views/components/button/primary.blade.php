@props(['type' => 'button', 'size' => 'md', 'variant' => 'primary'])

@php
$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
    'xl' => 'px-8 py-4 text-lg'
];

$variantClasses = [
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    'secondary' => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
    'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'warning' => 'bg-amber-600 text-white hover:bg-amber-700 focus:ring-amber-500',
    'outline' => 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-blue-500',
];

$classes = $sizeClasses[$size] . ' ' . $variantClasses[$variant];
@endphp

<button 
    type=\"{{ $type }}\"
    {{ $attributes->merge(['class' => \"inline-flex items-center justify-center rounded-xl font-medium shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed {$classes}\"]) }}
>
    {{ $slot }}
</button>