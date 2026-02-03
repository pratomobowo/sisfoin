<div>
    @if($availableRoles->count() > 1)
        <div class="space-y-0.5 px-1 py-1">
            @foreach($availableRoles as $role)
                <button wire:click="switchRole('{{ $role->name }}')" 
                        type="button"
                        class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-all duration-200 group
                            {{ $role->name === $currentRole 
                                ? 'bg-blue-50 text-blue-700 font-bold' 
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <div class="w-2 h-2 rounded-full mr-3 {{ $role->name === $currentRole ? 'bg-blue-500 animate-pulse' : 'bg-gray-300 group-hover:bg-gray-400' }}"></div>
                    {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                    @if($role->name === $currentRole)
                        <x-lucide-check class="w-4 h-4 ml-auto text-blue-600" />
                    @endif
                </button>
            @endforeach
        </div>
    @endif
</div>
