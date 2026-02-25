@props([
    'breadcrumbs' => [],
    'items' => [],
    'showHome' => false,
    'homeLabel' => 'Dashboard',
    'homeUrl' => null,
])

@php
    $normalized = [];

    if (!empty($items)) {
        foreach ($items as $item) {
            $label = $item['title'] ?? null;
            if (!$label) {
                continue;
            }

            $normalized[] = [
                'label' => $label,
                'url' => $item['url'] ?? null,
            ];
        }
    } else {
        foreach ($breadcrumbs as $label => $url) {
            $normalized[] = [
                'label' => $label,
                'url' => $url,
            ];
        }
    }

    if ($showHome) {
        array_unshift($normalized, [
            'label' => $homeLabel,
            'url' => $homeUrl,
        ]);
    }
@endphp

<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2">
        @foreach($normalized as $crumb)
            <li class="inline-flex items-center">
                @if(!$loop->last)
                    @if(!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors whitespace-nowrap">
                            {{ $crumb['label'] }}
                        </a>
                    @else
                        <span class="text-sm font-medium text-gray-500 whitespace-nowrap">
                            {{ $crumb['label'] }}
                        </span>
                    @endif
                    <span class="text-gray-400 mx-2">&gt;</span>
                @else
                    <span class="text-sm font-semibold text-gray-400 whitespace-nowrap">
                        {{ $crumb['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
