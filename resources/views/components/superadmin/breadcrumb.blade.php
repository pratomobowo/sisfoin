@props([
    'items' => []
])

<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-2">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">
                Dashboard
            </a>
        </li>
        
        @foreach($items as $item)
            <li class="flex items-center">
                <span class="text-gray-400 mx-2">&gt;</span>
                @if(isset($item['url']) && $item['url'])
                    <a href="{{ $item['url'] }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">
                        {{ $item['title'] }}
                    </a>
                @else
                    <span class="text-sm font-medium text-gray-500">
                        {{ $item['title'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
