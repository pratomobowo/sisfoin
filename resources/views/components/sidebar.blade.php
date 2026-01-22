<div class="w-72 bg-white border-r border-gray-100 fixed h-full z-40 flex flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0"
     x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
     x-show="true" {{-- Always rendered but hidden via transform --}}
     x-cloak>
    <!-- Logo & Brand -->
    <div class="flex items-center justify-between h-20 px-6 border-b border-gray-50">
        <div class="flex items-center space-x-3">
            <div class="p-1.5 bg-blue-50 rounded-xl">
                <img src="{{ asset('images/logo-usbypkp.jpg') }}" alt="USB PKP Logo" class="w-8 h-8 rounded-lg object-cover">
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-bold text-gray-900 leading-tight">USBYPKP</span>
                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-widest">Administration</span>
            </div>
        </div>
        
        <!-- Close button for mobile -->
        <button @click="sidebarOpen = false" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-gray-900 hover:bg-gray-100">
            <x-lucide-x class="w-5 h-5" />
        </button>
    </div>

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto py-6 px-4 space-y-8 scrollbar-hide">
        <!-- Main Dashboard -->
        <div>
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4">Dashboard</p>
            <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                <x-slot name="icon">
                    <x-lucide-layout-dashboard class="w-5 h-5" />
                </x-slot>
                Dasbor Utama
            </x-sidebar-link>
        </div>

        <!-- Admin Section -->
        @if(isActiveRole('super-admin'))
        <div>
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4">Administrasi</p>
            <div class="space-y-1">
                <x-sidebar-link href="{{ route('superadmin.users.index') }}" :active="request()->routeIs('superadmin.users.*')">
                    <x-slot name="icon">
                        <x-lucide-users class="w-5 h-5" />
                    </x-slot>
                    Manajemen Pengguna
                </x-sidebar-link>
                
                <x-sidebar-link href="{{ route('superadmin.roles.index') }}" :active="request()->routeIs('superadmin.roles.*')">
                    <x-slot name="icon">
                        <x-lucide-shield-check class="w-5 h-5" />
                    </x-slot>
                    Manajemen Peran
                </x-sidebar-link>

                <x-sidebar-link href="{{ route('superadmin.activity-logs.index') }}" :active="request()->routeIs('superadmin.activity-logs.index')">
                    <x-slot name="icon">
                        <x-lucide-history class="w-5 h-5" />
                    </x-slot>
                    Log Aktivitas
                </x-sidebar-link>

                <x-sidebar-link href="{{ route('superadmin.system-services.index') }}" :active="request()->routeIs('superadmin.system-services.index')">
                    <x-slot name="icon">
                        <x-lucide-cpu class="w-5 h-5" />
                    </x-slot>
                    System Services
                </x-sidebar-link>
                
                <!-- Email Group -->
                <div x-data="{ open: {{ request()->routeIs('superadmin.smtp.*') || request()->routeIs('superadmin.email-log.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-gray-600 rounded-xl hover:bg-gray-50 transition-all duration-200">
                        <div class="flex items-center">
                            <x-lucide-mail class="w-5 h-5 mr-3 text-gray-400" />
                            Layanan Email
                        </div>
                        <x-lucide-chevron-down class="w-4 h-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" x-cloak class="mt-1 ml-4 space-y-1 border-l-2 border-gray-50 pl-4">
                        <a href="{{ route('superadmin.smtp.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('superadmin.smtp.*') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Konfigurasi SMTP</a>
                        <a href="{{ route('superadmin.email-log.index') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('superadmin.email-log.index') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Log Email</a>
                    </div>
                </div>

                <!-- Fingerprint Log -->
                <x-sidebar-link href="{{ route('superadmin.fingerprint.attendance-logs.index') }}" :active="request()->routeIs('superadmin.fingerprint.attendance-logs.*')">
                    <x-slot name="icon">
                        <x-lucide-fingerprint class="w-5 h-5" />
                    </x-slot>
                    Data Absensi
                </x-sidebar-link>
            </div>
        </div>
        @endif

        <!-- Modules Sections -->
        <div>
            <p class="px-4 text-[11px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4">Modul Sistem</p>
            <div class="space-y-2">
                @if(isActiveRole('super-admin|admin-sdm'))
                <div x-data="{ open: {{ request()->routeIs('sdm.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-700 rounded-xl hover:bg-blue-50/50 transition-all duration-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg mr-3">
                                <x-lucide-briefcase class="w-5 h-5" />
                            </div>
                            Biro SDM
                        </div>
                        <x-lucide-chevron-down class="w-4 h-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" x-cloak class="mt-2 ml-10 space-y-1 border-l border-gray-100 pl-4">
                        <a href="{{ route('sdm.employees.index') }}" class="block py-2 text-sm {{ request()->routeIs('sdm.employees.*') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600 transition-colors' }}">Data Karyawan</a>
                        <a href="{{ route('sdm.dosens.index') }}" class="block py-2 text-sm {{ request()->routeIs('sdm.dosens.*') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600 transition-colors' }}">Data Dosen</a>
                        <a href="{{ route('sdm.slip-gaji.index') }}" class="block py-2 text-sm {{ request()->routeIs('sdm.slip-gaji.*') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600 transition-colors' }}">Slip Gaji</a>
                        
                        <div class="pt-4 pb-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Absensi</p>
                        </div>
                        <a href="{{ route('sdm.absensi.management') }}" class="block py-2 text-sm {{ request()->routeIs('sdm.absensi.management') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600 transition-colors' }}">Manajemen Absensi</a>
                        <a href="{{ route('sdm.absensi.recap') }}" class="block py-2 text-sm {{ request()->routeIs('sdm.absensi.recap') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600 transition-colors' }}">Rekap Bulanan</a>
                        <a href="{{ route('sdm.absensi.logs') }}" class="block py-2 text-sm {{ request()->routeIs('sdm.absensi.logs') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600 transition-colors' }}">Log Mesin</a>
                        <a href="{{ route('sdm.absensi.shifts') }}" class="block py-2 text-sm {{ request()->routeIs('sdm.absensi.shifts') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600 transition-colors' }}">Definisi Shift</a>
                        <a href="{{ route('sdm.absensi.kelola-shift') }}" class="block py-2 text-sm {{ request()->routeIs('sdm.absensi.kelola-shift') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600 transition-colors' }}">Kelola Shift Unit</a>
                        <a href="{{ route('sdm.absensi.settings') }}" class="block py-2 text-sm {{ request()->routeIs('sdm.absensi.settings') ? 'text-blue-600 font-semibold' : 'text-gray-500 hover:text-blue-600 transition-colors' }}">Pengaturan</a>
                    </div>
                </div>
                @endif

                @if(isActiveRole('employee|staff'))
                <div x-data="{ open: {{ request()->routeIs('staff.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-700 rounded-xl hover:bg-emerald-50/50 transition-all duration-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg mr-3">
                                <x-lucide-user class="w-5 h-5" />
                            </div>
                            Layanan Mandiri
                        </div>
                        <x-lucide-chevron-down class="w-4 h-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>
                    <div x-show="open" x-cloak class="mt-2 ml-10 space-y-1 border-l border-emerald-100 pl-4">
                        <a href="{{ route('staff.penggajian.index') }}" class="block py-2 text-sm {{ request()->routeIs('staff.penggajian.*') ? 'text-emerald-600 font-semibold' : 'text-gray-500 hover:text-emerald-600 transition-colors' }}">Informasi Gaji</a>
                        <a href="{{ route('staff.absensi.index') }}" class="block py-2 text-sm {{ request()->routeIs('staff.absensi.*') ? 'text-emerald-600 font-semibold' : 'text-gray-500 hover:text-emerald-600 transition-colors' }}">Riwayat Absensi</a>
                        <a href="{{ route('staff.pengumuman.index') }}" class="block py-2 text-sm {{ request()->routeIs('staff.pengumuman.*') ? 'text-emerald-600 font-semibold' : 'text-gray-500 hover:text-emerald-600 transition-colors' }}">Pengumuman</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- User Profile Quick View -->
    <div class="p-4 border-t border-gray-50">
        <div class="bg-gray-50/50 rounded-2xl p-4 flex items-center space-x-3">
            <div class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold shadow-sm">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] font-medium text-gray-500 truncate uppercase">{{ ucfirst(str_replace('-', ' ', getActiveRole())) }}</p>
            </div>
        </div>
    </div>
</div>
