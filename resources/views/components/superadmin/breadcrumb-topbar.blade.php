@props([
    'items' => []
])

<nav class="flex items-center space-x-2 text-sm" aria-label="Breadcrumb">
    @foreach($items as $index => $item)
        @if(!$loop->first)
            <span class="text-gray-400">&gt;</span>
        @endif
        
        <!-- Item -->
        @if(isset($item['url']) && $item['url'])
            <a href="{{ $item['url'] }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                {{ $item['title'] }}
            </a>
        @else
            <span class="text-gray-900 font-medium">
                {{ $item['title'] }}
            </span>
        @endif
    @endforeach
</nav>
