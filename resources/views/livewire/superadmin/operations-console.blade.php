<div class="space-y-6">
    <x-page-header
        title="Operations Console"
        subtitle="Trigger command maintenance superadmin secara aman"
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Operations Console' => route('superadmin.operations-console.index')]"
    />

    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-xl p-4 space-y-4">
            @foreach($commandGroups as $groupName => $commands)
                <div>
                    <h3 class="text-xs uppercase tracking-wider font-semibold text-gray-500 mb-2">{{ $groupName }}</h3>
                    <div class="space-y-1">
                        @foreach($commands as $cmd)
                            <button
                                type="button"
                                wire:click="$set('selectedCommand', '{{ $cmd }}')"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm border {{ $selectedCommand === $cmd ? 'border-blue-200 bg-blue-50 text-blue-700 font-semibold' : 'border-gray-200 hover:bg-gray-50 text-gray-700' }}"
                            >
                                {{ $cmd }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ $selectedCommand }}</h3>
                <p class="text-xs text-gray-500">Gunakan dengan hati-hati. Semua eksekusi akan tercatat di activity log.</p>
            </div>

            @if($selectedCommand === 'users:relink-employee-links')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-600">Type</label>
                        <select wire:model="commandOptions.relink_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="all">all</option>
                            <option value="employee">employee</option>
                            <option value="dosen">dosen</option>
                        </select>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-6">
                        <input type="checkbox" wire:model="commandOptions.relink_fill_nip"> fill-nip
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-6">
                        <input type="checkbox" wire:model="commandOptions.relink_dry_run"> dry-run
                    </label>
                </div>
            @elseif($selectedCommand === 'attendance:process')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-600">Date From</label>
                        <input type="date" wire:model="commandOptions.attendance_date_from" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Date To</label>
                        <input type="date" wire:model="commandOptions.attendance_date_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">User ID (optional)</label>
                        <input type="number" wire:model="commandOptions.attendance_user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 md:col-span-3">
                        <input type="checkbox" wire:model="commandOptions.attendance_force"> force
                    </label>
                </div>
            @elseif($selectedCommand === 'users:import')
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" wire:model="commandOptions.users_import_force"> force update existing users
                </label>
            @elseif($selectedCommand === 'sevima:sync-test')
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" wire:model="commandOptions.sevima_sync_confirm"> Saya paham ini sync penuh dan berisiko
                </label>
                @error('commandOptions.sevima_sync_confirm')
                    <div class="text-xs text-red-600">{{ $message }}</div>
                @enderror
            @endif

            <div class="border-t pt-4 space-y-2">
                <label class="text-xs text-gray-600">Ketik RUN untuk konfirmasi</label>
                <input type="text" wire:model="confirmationText" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="RUN">
                @error('confirmationText')
                    <div class="text-xs text-red-600">{{ $message }}</div>
                @enderror
                <button wire:click="runCommand" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    Jalankan Command
                </button>
            </div>
        </div>
    </div>

    @if(!empty($lastRun['run_at']))
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h4 class="font-semibold text-gray-800">Hasil Eksekusi Terakhir</h4>
                <span class="text-xs {{ $lastRun['status'] === 'success' ? 'text-green-600' : 'text-red-600' }}">{{ strtoupper($lastRun['status']) }}</span>
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ $lastRun['command'] }} | {{ $lastRun['run_at'] }} | exit {{ $lastRun['exit_code'] }}</div>
            <pre class="mt-3 bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-700 overflow-auto max-h-80">{{ $lastRun['output'] ?: '-' }}</pre>
        </div>
    @endif
</div>
