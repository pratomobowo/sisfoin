<?php

namespace App\Livewire\Sdm;

use App\Models\AttendanceSetting;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AttendanceSettingsManager extends Component
{
    public $settings = [];
    public $workingDays = [];
    public $showSuccessMessage = false;

    protected $dayLabels = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $allSettings = AttendanceSetting::orderBy('group')
            ->orderBy('sort_order')
            ->get();

        foreach ($allSettings as $setting) {
            if ($setting->key === 'working_days') {
                $this->workingDays = explode(',', $setting->value);
            } else {
                $this->settings[$setting->key] = $setting->value;
            }
        }
    }

    public function save()
    {
        // Save regular settings
        foreach ($this->settings as $key => $value) {
            AttendanceSetting::where('key', $key)->update(['value' => $value]);
        }

        // Save working days
        if (!empty($this->workingDays)) {
            AttendanceSetting::where('key', 'working_days')
                ->update(['value' => implode(',', $this->workingDays)]);
        }

        // Clear cache
        AttendanceSetting::clearCache();

        session()->flash('success', 'Pengaturan absensi berhasil disimpan!');
        
        $this->dispatch('settings-saved');
    }

    public function getDayLabelsProperty()
    {
        return $this->dayLabels;
    }

    public function render()
    {
        $groupedSettings = AttendanceSetting::where('key', '!=', 'working_days')
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        return view('livewire.sdm.attendance-settings-manager', [
            'groupedSettings' => $groupedSettings,
        ]);
    }
}
