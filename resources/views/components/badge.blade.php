@props([
    'variant' => 'default', // default, success, warning, error, info
    'size' => 'sm' // xs, sm, md
])

@php
$variantClasses = [
    'default' => 'bg-gray-100 text-gray-800',
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'error' => 'bg-red-100 text-red-800',
    'info' => 'bg-blue-100 text-blue-800',
];

$sizeClasses = [
    'xs' => 'px-2 py-0.5 text-xs',
    'sm' => 'px-2.5 py-0.5 text-sm',
    'md' => 'px-3 py-1 text-base',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center font-medium rounded-full ' . $variantClasses[$variant] . ' ' . $sizeClasses[$size]]) }}>
    {{ $slot }}
</span>