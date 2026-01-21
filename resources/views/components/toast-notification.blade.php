@php
    $sessionMessages = collect([
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
    ])->filter()->toArray();
@endphp

<!-- Toast Container -->
<div x-data="toastManager()" 
     x-init="
        @foreach($sessionMessages as $type => $message)
            addToast({ message: '{{ $message }}', type: '{{ $type }}' });
        @endforeach
     "
     @toast-show.window="addToast($event.detail)"
     @notify.window="addToast($event.detail)"
     @show-toast.window="addToast($event.detail)"
     class="fixed top-6 right-6 z-[10000] flex flex-col items-end space-y-4 max-w-md w-full pointer-events-none"
     x-cloak>
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.show"
             x-transition:enter="transform transition ease-out duration-500"
             x-transition:enter-start="translate-y-4 opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transform transition ease-in duration-300"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="pointer-events-auto w-full bg-white/90 backdrop-blur-md border border-gray-100 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] p-4 flex items-start overflow-hidden relative group">
            
            <!-- Type Indicator Bar -->
            <div class="absolute left-0 top-0 bottom-0 w-1.5"
                 :class="{
                    'bg-emerald-500': toast.type === 'success',
                    'bg-rose-500': toast.type === 'error' || toast.type === 'danger',
                    'bg-amber-500': toast.type === 'warning',
                    'bg-blue-500': toast.type === 'info'
                 }">
            </div>

            <!-- Icon -->
            <div class="flex-shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <div class="p-1.5 bg-emerald-50 rounded-lg">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'error' || toast.type === 'danger'">
                    <div class="p-1.5 bg-rose-50 rounded-lg">
                        <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'warning'">
                    <div class="p-1.5 bg-amber-50 rounded-lg">
                        <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'info'">
                    <div class="p-1.5 bg-blue-50 rounded-lg">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </template>
            </div>
            
            <!-- Content -->
            <div class="ml-3 flex-1 pt-0.5">
                <p class="text-sm font-semibold text-gray-900" 
                   x-text="toast.type.charAt(0).toUpperCase() + toast.type.slice(1)"></p>
                <p class="text-sm text-gray-600 mt-1 leading-relaxed" x-text="toast.message"></p>
            </div>
            
            <!-- Close Button -->
            <button @click="removeToast(toast.id)" 
                    class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors group-hover:opacity-100 lg:opacity-0">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Auto-hide progress bar (optional fancy effect) -->
            <div class="absolute bottom-0 left-0 h-0.5 bg-gray-100 w-full overflow-hidden">
                <div class="h-full" 
                     :class="{
                        'bg-emerald-500/30': toast.type === 'success',
                        'bg-rose-500/30': toast.type === 'error' || toast.type === 'danger',
                        'bg-amber-500/30': toast.type === 'warning',
                        'bg-blue-500/30': toast.type === 'info'
                     }"
                     :style="`animation: toast-progress 5s linear forwards`"
                ></div>
            </div>
        </div>
    </template>
</div>

<style>
    @keyframes toast-progress {
        from { width: 100%; }
        to { width: 0%; }
    }
</style>

<script>
function toastManager() {
    return {
        toasts: [],
        addToast(detail) {
            // Handle both simple string and object with details
            const toast = typeof detail === 'string' 
                ? { message: detail, type: 'info' } 
                : (detail.detail ? detail.detail : detail); // Handle Livewire event nesting
            
            const id = Date.now() + Math.random();
            
            this.toasts.push({
                id: id,
                message: toast.message || toast.text || 'No message provided',
                type: toast.type || 'info',
                show: false
            });

            // Trigger enter animation
            this.$nextTick(() => {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) this.toasts[index].show = true;
            });
            
            // Auto hide
            setTimeout(() => {
                this.removeToast(id);
            }, 5000);
        },
        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                this.toasts[index].show = false;
                // Wait for transition before removal
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        }
    }
}
</script>
