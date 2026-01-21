@props(['label' => null, 'error' => null, 'required' => false, 'help' => null])

@php
$hasError = $error || $errors->has($attributes->get('name'));
$errorMessage = $error ?? $errors->first($attributes->get('name'));
$inputId = $attributes->get('id') ?? 'input_' . str()->random(8);
@endphp

<div class=\"space-y-2\">
    @if($label)
        <label for=\"{{ $inputId }}\" class=\"block text-sm font-medium text-gray-700\">
            {{ $label }}
            @if($required)
                <span class=\"text-red-500\">*</span>
            @endif
        </label>
    @endif
    
    <input 
        id=\"{{ $inputId }}\"
        {{ $attributes->merge(['class' => 'w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent transition-colors ' . ($hasError ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500')]) }}
    >
    
    @if($hasError)
        <p class=\"text-sm text-red-600\">{{ $errorMessage }}</p>
    @endif
    
    @if($help && !$hasError)
        <p class=\"text-sm text-gray-500\">{{ $help }}</p>
    @endif
</div>