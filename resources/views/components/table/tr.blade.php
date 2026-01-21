@props(['striped' => false])

@php
$index = $attributes->get('data-index', 0);
$stripedClass = ($striped && $index % 2 === 0) ? 'bg-gray-50' : '';
@endphp

<tr {{ $attributes->merge(['class' => \"hover:bg-gray-50 transition-colors {$stripedClass}\"]) }}>
    {{ $slot }}
</tr>