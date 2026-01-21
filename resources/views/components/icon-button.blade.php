@props(['type' => 'view', 'tooltip' => null, 'href' => null])

@php
$colors = [
    'view' => 'text-blue-600 hover:bg-blue-50',
    'edit' => 'text-green-600 hover:bg-green-50',
    'delete' => 'text-red-600 hover:bg-red-50',
    'info' => 'text-gray-600 hover:bg-gray-50'
];

$icons = [
    'view' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
    'edit' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    'delete' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
    'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
];

$colorClass = $colors[$type] ?? $colors['view'];
$iconPath = $icons[$type] ?? $icons['view'];
@endphp

@if($href)
    <a 
        href=\"{{ $href }}\"
        title=\"{{ $tooltip }}\"
        aria-label=\"{{ $tooltip }}\"
        {{ $attributes->merge(['class' => \"inline-flex p-2 rounded-lg transition-colors {$colorClass}\"]) }}
    >
        <svg class=\"w-5 h-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"{{ $iconPath }}\"></path>
        </svg>
    </a>
@else
    <button 
        type=\"button\"
        title=\"{{ $tooltip }}\"
        aria-label=\"{{ $tooltip }}\"
        {{ $attributes->merge(['class' => \"inline-flex p-2 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {$colorClass}\"]) }}
    >
        <svg class=\"w-5 h-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"{{ $iconPath }}\"></path>
        </svg>
    </button>
@endif