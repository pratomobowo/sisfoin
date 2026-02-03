<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - @yield('page-title')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Livewire Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-gray-50/50">
            <!-- Sidebar -->
            <x-sidebar />

            <!-- Backdrop for mobile -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-on:click="sidebarOpen = false"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 lg:hidden"
                 x-cloak></div>

            <!-- Main Content Area -->
            <div class="flex-1 lg:ml-72 flex flex-col min-h-screen transition-all duration-300">
                <!-- Header (Placeholder for later) -->
                <!-- Top bar -->
                <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-30 font-medium font-sans">


                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center h-16">
                            <!-- Mobile menu button -->
                            <div class="flex items-center lg:hidden">
                                <button x-on:click.stop="sidebarOpen = true" class="p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                </button>
                            </div>
                            <!-- Breadcrumb -->
                            @hasSection('breadcrumb')
                                <div class="flex-1 flex items-center min-w-0 pr-4">
                                    @yield('breadcrumb')
                                </div>
                            @endif
                            
                            <!-- User menu -->
                            <div class="flex items-center space-x-4">

                                <!-- User dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" 
                                            class="flex items-center space-x-4 text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg p-2"
                                            aria-expanded="false" aria-haspopup="true">
                                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">
                                                {{ substr(auth()->user()->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="hidden lg:block text-sm font-medium max-w-40 truncate">{{ auth()->user()->name }}</span>
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <div x-show="open" 
                                         @click.away="open = false"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                        
                                        <a href="{{ route('dashboard') }}" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Dasbor Utama
                                        </a>
                                        
                                        <a href="{{ isActiveRole('staff|employee') ? route('staff.profile') : route('profile.edit') }}" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Pengaturan Profil
                                        </a>

                                        <hr class="my-1">
                                        
                                        <!-- Role Switcher Integrated -->
                                        <livewire:role-switcher />

                                        
                                        <hr class="my-1">
                                        
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Keluar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page content -->
                <main class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        @yield('content')
                        {{ $slot ?? '' }}
                    </div>
                </main>
            </div>
        </div>
        
        <!-- Toast Notifications -->
        <x-toast-notification />
        
        <!-- Livewire Scripts -->
        @livewireScripts
    </body>
</html>
