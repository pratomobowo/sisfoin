<?php

namespace App\Livewire\Superadmin;

use App\Models\SystemService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SystemServices extends Component
{
    public function toggleService($serviceId)
    {
        $service = SystemService::findOrFail($serviceId);
        $service->is_active = !$service->is_active;
        $service->status = $service->is_active ? 'running' : 'stopped';
        $service->save();

        $status = $service->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Service {$service->name} berhasil {$status}.");
    }

    public function runService($serviceId)
    {
        $service = SystemService::findOrFail($serviceId);
        
        // Simulating running a service
        $service->last_run_at = now();
        $service->status = 'running';
        $service->save();

        session()->flash('success', "Service {$service->name} telah dijalankan secara manual.");
    }

    public function render()
    {
        $services = SystemService::all();
        
        return view('livewire.superadmin.system-services', [
            'services' => $services
        ]);
    }
}
