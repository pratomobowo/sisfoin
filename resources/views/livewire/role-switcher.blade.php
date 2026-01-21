<div class="relative">
    @if($availableRoles->count() > 1)
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Peran:</span>
            <select wire:change="switchRole($event.target.value)" 
                    class="px-3 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @foreach($availableRoles as $role)
                    <option value="{{ $role->name }}" @if($role->name === $currentRole) selected @endif>
                        {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                    </option>
                @endforeach
            </select>
        </div>
    @else
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Peran:</span>
            <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-lg">
                {{ ucfirst(str_replace('-', ' ', $currentRole)) }}
            </span>
        </div>
    @endif
</div>
