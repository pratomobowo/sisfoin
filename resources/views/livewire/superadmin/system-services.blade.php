<div>
    <x-page-header 
        title="System Services" 
        subtitle="Manajemen layanan latar belakang dan proses otomatis sistem"
        :breadcrumbs="[
            'Dashboard' => route('dashboard'),
            'System Services' => request()->url()
        ]">
    </x-page-header>

    <div class="mt-6 space-y-6">
        <!-- Alert Messages -->
        @if(session()->has('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center shadow-sm">
                <x-lucide-check-circle class="w-5 h-5 mr-3 text-green-500" />
                <p class="text-sm font-medium">{{ session('success') }}</p>
                <button type="button" onclick="this.parentElement.remove()" class="ml-auto text-green-500 hover:text-green-700">
                    <x-lucide-x class="w-4 h-4" />
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $service)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col transition-all duration-300 hover:shadow-md hover:border-blue-100">
                    <div class="p-6 flex-1">
                        <div class="flex items-start justify-between mb-4">
                            <div class="p-3 {{ $service->is_active ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-400' }} rounded-xl">
                                @if($service->key === 'email_queue')
                                    <x-lucide-mail class="w-6 h-6" />
                                @elseif($service->key === 'fingerprint_sync')
                                    <x-lucide-fingerprint class="w-6 h-6" />
                                @elseif($service->key === 'attendance_processor')
                                    <x-lucide-cpu class="w-6 h-6" />
                                @elseif($service->key === 'system_backup')
                                    <x-lucide-database class="w-6 h-6" />
                                @else
                                    <x-lucide-box class="w-6 h-6" />
                                @endif
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    {{ $service->status === 'running' ? 'bg-emerald-100 text-emerald-700' : ($service->status === 'error' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $service->status }}
                                </span>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $service->name }}</h3>
                        <p class="text-sm text-gray-500 mb-4 line-clamp-2">
                            {{ $service->description }}
                        </p>

                        <div class="space-y-3 pt-4 border-t border-gray-50 uppercase tracking-widest text-[10px] font-bold text-gray-400">
                            <div class="flex justify-between items-center">
                                <span>Status Layanan</span>
                                <span class="{{ $service->is_active ? 'text-emerald-600' : 'text-gray-400' }}">
                                    {{ $service->is_active ? 'ENABLED' : 'DISABLED' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Terakhir Jalan</span>
                                <span class="text-gray-600 italic">
                                    {{ $service->last_run_at ? $service->last_run_at->diffForHumans() : 'Belum pernah' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
                        <button wire:click="runService({{ $service->id }})" 
                                class="flex items-center space-x-2 text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors uppercase tracking-widest px-3 py-2 rounded-lg hover:bg-blue-50">
                            <x-lucide-play class="w-4 h-4" />
                            <span>Jalankan</span>
                        </button>

                        <button wire:click="toggleService({{ $service->id }})" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 {{ $service->is_active ? 'bg-blue-600' : 'bg-gray-200' }}">
                            <span class="sr-only">Toggle feature</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $service->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Info Card -->
        <div class="bg-blue-600 rounded-2xl p-8 text-white relative overflow-hidden shadow-lg">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-xl">
                    <h2 class="text-2xl font-bold mb-2">Automasi & Latar Belakang</h2>
                    <p class="text-blue-100">
                        Halaman ini digunakan untuk memantau dan mengontrol proses otomatis yang berjalan di server. Menonaktifkan layanan tertentu dapat menghemat resource server namun fungsionalitas terkait akan terhenti.
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/20">
                        <div class="flex items-center space-x-3 text-sm">
                            <x-lucide-info class="w-5 h-5" />
                            <span>Pastikan <strong>Cron Job</strong> server aktif.</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Decorative circle -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-48 h-48 bg-blue-400/20 rounded-full blur-3xl"></div>
        </div>
    </div>
</div>
