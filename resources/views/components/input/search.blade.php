@props(['placeholder' => 'Search...', 'icon' => true])

<div class=\"relative\">
    @if($icon)
        <div class=\"absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none\">
            <svg class=\"w-5 h-5 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z\"></path>
            </svg>
        </div>
    @endif
    
    <input 
        type=\"text\"
        placeholder=\"{{ $placeholder }}\"
        {{ $attributes->merge(['class' => 'w-full border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors ' . ($icon ? 'pl-10 pr-4 py-2' : 'px-4 py-2')]) }}
    >
</div>