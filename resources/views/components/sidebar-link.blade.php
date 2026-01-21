@props(['active' => false, 'href', 'icon' => null])

@php
$classes = $active
            ? 'flex items-center px-4 py-2.5 text-sm font-semibold text-blue-600 bg-blue-50/50 rounded-xl transition-all duration-200 shadow-sm shadow-blue-100/50 border border-blue-100/50 group'
            : 'flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 group';

$iconClasses = "w-5 h-5 mr-3 " . ($active ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600') . " transition-colors";
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        <div class="{{ $iconClasses }} flex items-center justify-center">
            {{ $icon }}
        </div>
    @elseif($icon)
        <svg class="{{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icon !!}
        </svg>
    @endif
    
    <span class="truncate">{{ $slot }}</span>
    
    @if($active)
        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-600"></span>
    @endif
</a>
