<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- Logo Section -->
            <div class="mb-6">
                <div class="flex items-center justify-center space-x-2">
                    <img src="{{ asset('images/logo-usbypkp.jpg') }}" alt="USB PKP Logo" class="w-12 h-12 rounded-xl shadow-lg object-cover">
                    <div class="text-2xl font-semibold text-gray-800">USBYPKP</div>
                </div>
            </div>

            <!-- Content Card -->
            <div class="w-full sm:max-w-md">
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-8 py-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-sm text-gray-500">
                © {{ date('Y') }} Universitas Sanggabuana Bandung. All rights reserved.
            </div>
        </div>
    </body>
</html>
