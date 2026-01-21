@props([
    'size' => 'md', // sm, md, lg
    'color' => 'blue', // blue, gray, white
])

@php
$sizeClasses = [
    'sm' => 'h-4 w-4',
    'md' => 'h-6 w-6',
    'lg' => 'h-8 w-8',
];

$colorClasses = [
    'blue' => 'text-blue-600',
    'gray' => 'text-gray-600',
    'white' => 'text-white',
];
@endphp

<div {{ $attributes->merge(['class' => 'inline-block animate-spin rounded-full border-2 border-solid border-current border-r-transparent motion-reduce:animate-[spin_1.5s_linear_infinite] ' . $sizeClasses[$size] . ' ' . $colorClasses[$color]]) }} role="status">
    <span class="sr-only">Loading...</span>
</div>