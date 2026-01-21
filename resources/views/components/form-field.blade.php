@props([
    'label' => '',
    'name' => '',
    'required' => false,
    'help' => '',
    'error' => '',
])

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if($label)
        <x-input-label for="{{ $name }}" :value="$label" :required="$required" />
    @endif
    
    <div>
        {{ $slot }}
    </div>
    
    @if($help)
        <p class="text-sm text-gray-500">{{ $help }}</p>
    @endif
    
    @if($error)
        <x-input-error :messages="$error" />
    @elseif($errors->has($name))
        <x-input-error :messages="$errors->get($name)" />
    @endif
</div>