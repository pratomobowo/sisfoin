@props(['striped' => false, 'bordered' => true])

@php
$classes = 'min-w-full divide-y divide-gray-200';
if ($bordered) {
    $classes .= ' border border-gray-200';
}
@endphp

<div class=\"overflow-x-auto bg-white rounded-xl shadow-sm\">
    <table {{ $attributes->merge(['class' => $classes]) }}>
        @isset($head)
            <thead class=\"bg-gray-50\">
                {{ $head }}
            </thead>
        @endisset
        
        @isset($body)
            <tbody class=\"{{ $striped ? 'divide-y divide-gray-100' : 'bg-white divide-y divide-gray-100' }}\">
                {{ $body }}
            </tbody>
        @endisset
        
        {{ $slot }}
    </table>
</div>