<?php

namespace App\Livewire\Superadmin;

use App\Models\SystemService;
use App\Models\SystemServiceExecutionLog;
use App\Services\SystemServiceRunner;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SystemServices extends Component
{
    use WithPagination;

    public array $schedulePresets = [];

    public int $perPage = 10;

    public function mount(): void
    {
        $this->schedulePresets = SystemService::SCHEDULE_PRESETS;
    }

    public function toggleService($serviceId)
    {
        $service = SystemService::findOrFail($serviceId);
        $service->is_active = ! $service->is_active;
        $service->status = $service->is_active ? 'running' : 'stopped';
        $service->save();

        $status = $service->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Service {$service->name} berhasil {$status}.");
    }

    public function runService($serviceId)
    {
        $service = SystemService::findOrFail($serviceId);

        $result = app(SystemServiceRunner::class)->run($service, 'manual', Auth::id());

        if ($result['success']) {
            session()->flash('success', "Service {$service->name} telah dijalankan secara manual.");
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function updateSchedulePreset($serviceId, $preset)
    {
        $service = SystemService::findOrFail($serviceId);

        if (! array_key_exists($preset, SystemService::SCHEDULE_PRESETS)) {
            session()->flash('error', 'Preset jadwal tidak valid.');

            return;
        }

        $service->schedule_preset = $preset;
        $service->save();

        session()->flash('success', "Jadwal {$service->name} diatur ke {$service->schedule_preset_label}.");
    }

    public function render()
    {
        $services = SystemService::all();
        $logs = SystemServiceExecutionLog::query()
            ->with('triggerUser:id,name')
            ->latest('started_at')
            ->paginate($this->perPage);

        return view('livewire.superadmin.system-services', [
            'services' => $services,
            'logs' => $logs,
        ]);
    }
}
