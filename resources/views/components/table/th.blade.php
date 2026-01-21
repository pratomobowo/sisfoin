@props(['sortable' => false, 'direction' => null])

<th {{ $attributes->merge(['class' => 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider']) }}>
    @if($sortable)
        <button class=\"flex items-center space-x-1 hover:text-gray-700 focus:outline-none focus:text-gray-700\">
            <span>{{ $slot }}</span>
            @if($direction === 'asc')
                <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 15l7-7 7 7\"></path>
                </svg>
            @elseif($direction === 'desc')
                <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 9l-7 7-7-7\"></path>
                </svg>
            @else
                <svg class=\"w-4 h-4 opacity-50\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M8 9l4-4 4 4m0 6l-4 4-4-4\"></path>
                </svg>
            @endif
        </button>
    @else
        {{ $slot }}
    @endif
</th>