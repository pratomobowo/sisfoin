<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Konfigurasi SMTP</h2>
                    <p class="mt-1 text-sm text-gray-600">Kelola pengaturan server email untuk mengirim notifikasi dan email dari sistem</p>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tab Navigation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button wire:click="$set('activeTab', 'setting')" 
                            class="{{ $activeTab === 'setting' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-4 h-4 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Setting
                    </button>
                    <button wire:click="$set('activeTab', 'test')" 
                            class="{{ $activeTab === 'test' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-4 h-4 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Test Email
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Setting Tab -->
                @if($activeTab === 'setting')
                    <div class="space-y-6">
                        <!-- Gmail Configuration -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Konfigurasi Gmail SMTP</h3>
                            <form wire:submit.prevent="save" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Email Gmail</label>
                                    <input type="email" wire:model="gmailEmail" 
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                           placeholder="your-email@gmail.com">
                                    <p class="mt-1 text-xs text-gray-500">Gunakan alamat Gmail atau Google Workspace Anda</p>
                                    @error('gmailEmail')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Gmail / App Password</label>
                                    <div class="relative">
                                        <input type="password" wire:model="gmailPassword" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="••••••••">
                                        @if($passwordSaved && empty($gmailPassword))
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Tersimpan
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Gunakan App Password jika Anda memiliki 2FA. 
                                        <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-blue-600 hover:underline">Generate App Password</a>
                                    </p>
                                    @if($passwordSaved && empty($gmailPassword))
                                        <p class="mt-1 text-xs text-blue-600">
                                            Password sudah tersimpan dengan aman. Kosongkan field ini jika tidak ingin mengubah password.
                                        </p>
                                    @endif
                                    @error('gmailPassword')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pengirim</label>
                                    <input type="text" wire:model="gmailFromName" 
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                           placeholder="USBYPKP System">
                                    @error('gmailFromName')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="isActive" id="isActive" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="isActive" class="ml-2 block text-sm text-gray-700">Aktifkan konfigurasi ini</label>
                                </div>
                                
                                <div class="pt-4">
                                    <!-- Info message for test connection -->
                                    @if($passwordSaved)
                                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 text-blue-700 rounded-md">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-blue-800">
                                                        <strong>Test Koneksi:</strong> Password sudah tersimpan di database. Anda dapat melakukan test koneksi tanpa mengisi ulang password, kecuali jika ingin mengubah password.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="flex flex-wrap gap-3">
                                        <button type="submit" wire:loading.attr="disabled" 
                                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg transition-colors">
                                            <span wire:loading.remove>Simpan Konfigurasi</span>
                                            <span wire:loading>Menyimpan...</span>
                                        </button>
                                        <button type="button" wire:click="testConnection" wire:loading.attr="disabled" 
                                                class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg transition-colors">
                                            <span wire:loading.remove>Test Koneksi</span>
                                            <span wire:loading>Testing...</span>
                                        </button>
                                        <button type="button" wire:click="toggleDebug" 
                                                class="{{ $showDebug ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-600 hover:bg-gray-700' }} text-white px-4 py-2 rounded-lg transition-colors">
                                            <span>{{ $showDebug ? 'Sembunyikan Debug' : 'Debug Info' }}</span>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Connection Test Result -->
                                @if ($connectionMessage)
                                    <div class="mt-4">
                                        <div class="rounded-lg p-3 @if ($connectionStatus == 'success') bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                            {{ $connectionMessage }}
                                        </div>
                                    </div>
                                @endif

                                <!-- Debug Information -->
                                @if ($showDebug)
                                    <div class="mt-4">
                                        <h4 class="text-md font-medium text-gray-900 mb-2">Debug Information</h4>
                                        <div class="bg-gray-100 border border-gray-300 rounded-lg p-4">
                                            @if ($debugMessage)
                                                <div class="rounded-lg p-3 @if ($debugStatus == 'success') bg-blue-100 text-blue-800 @else bg-red-100 text-red-800 @endif mb-3">
                                                    <pre class="text-xs whitespace-pre-wrap">{{ $debugMessage }}</pre>
                                                </div>
                                            @endif
                                            <div class="text-xs text-gray-600">
                                                <p class="mb-2"><strong>Cara menggunakan debug info:</strong></p>
                                                <ul class="list-disc list-inside space-y-1">
                                                    <li>Periksa apakah konfigurasi mail sudah benar</li>
                                                    <li>Pastikan username dan password sudah terisi dengan benar</li>
                                                    <li>Cek apakah transport class sesuai</li>
                                                    <li>Jika ada error, periksa log file untuk detail lebih lanjut</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Test Email Tab -->
                @if($activeTab === 'test')
                    <div class="space-y-6">
                        <!-- Send Test Email -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Kirim Email Test</h3>
                            <form wire:submit.prevent="sendTestEmail" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Tujuan Test</label>
                                        <input type="email" wire:model="testEmail" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="test@example.com">
                                        @error('testEmail')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                        <input type="text" wire:model="testSubject" 
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
                                    <textarea wire:model="testMessage" rows="4" 
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                              placeholder="Ini adalah email test..."></textarea>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        @if ($emailMessage)
                                            <div class="rounded-lg p-3 @if ($emailStatus == 'success') bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                                {{ $emailMessage }}
                                            </div>
                                        @endif
                                    </div>
                                    <button type="submit" wire:loading.attr="disabled" 
                                            class="ml-4 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg transition-colors">
                                        <span wire:loading.remove>Kirim Email Test</span>
                                        <span wire:loading>Mengirim...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
