@props([
    'method' => 'POST',
    'action' => '',
    'hasFiles' => false,
])

<form 
    @if($action) action="{{ $action }}" @endif
    method="{{ $method === 'GET' ? 'GET' : 'POST' }}"
    @if($hasFiles) enctype="multipart/form-data" @endif
    {{ $attributes->merge(['class' => 'space-y-6']) }}
>
    @if(!in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif
    
    @if($method !== 'GET')
        @csrf
    @endif
    
    {{ $slot }}
</form>