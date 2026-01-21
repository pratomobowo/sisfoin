@props([
    'label' => null,
    'name' => '',
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'help' => null,
    'error' => null,
    'size' => 'md'
])

@php
    $inputId = $name ?: 'select_' . uniqid();
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?: ($errors->has($name) ? $errors->first($name) : null);
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-3 py-2 text-sm',
        'lg' => 'px-4 py-3 text-base'
    ];
    
    $baseClasses = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 ' . $sizes[$size];
    
    if ($hasError) {
        $baseClasses .= ' border-red-500 focus:border-red-500 focus:ring-red-500';
    }
    
    if ($disabled) {
        $baseClasses .= ' bg-gray-50 text-gray-500 cursor-not-allowed';
    }
    
    $selectedValue = old($name, $value);
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <select 
        id="{{ $inputId }}"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        {{ $attributes->except(['class', 'label', 'name', 'value', 'options', 'placeholder', 'required', 'disabled', 'multiple', 'help', 'error', 'size'])->merge(['class' => $baseClasses]) }}
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($multiple) multiple @endif
    >
        @if($placeholder && !$multiple)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @if(is_array($options) || is_object($options))
            @foreach($options as $optionValue => $optionLabel)
                @if(is_array($optionLabel))
                    <optgroup label="{{ $optionValue }}">
                        @foreach($optionLabel as $subValue => $subLabel)
                            <option value="{{ $subValue }}" 
                                @if($multiple && is_array($selectedValue) && in_array($subValue, $selectedValue)) selected
                                @elseif(!$multiple && $subValue == $selectedValue) selected
                                @endif>
                                {{ $subLabel }}
                            </option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $optionValue }}" 
                        @if($multiple && is_array($selectedValue) && in_array($optionValue, $selectedValue)) selected
                        @elseif(!$multiple && $optionValue == $selectedValue) selected
                        @endif>
                        {{ $optionLabel }}
                    </option>
                @endif
            @endforeach
        @endif
        
        {{ $slot }}
    </select>
    
    @if($help && !$hasError)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif
    
    @if($hasError)
        <p class="mt-1 text-sm text-red-600">{{ $errorMessage }}</p>
    @endif
</div>