@props([
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'empty' => false,
])

<select {{ $attributes->merge(['class' => 'font-poppins border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm transition duration-150 ease-in-out']) }}>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    
    @if($empty)
        <option value="">-- None --</option>
    @endif
    
    @if(is_array($options))
        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    @else
        {{ $slot }}
    @endif
</select>