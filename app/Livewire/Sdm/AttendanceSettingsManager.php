<?php

namespace App\Livewire\Sdm;

use App\Livewire\Concerns\InteractsWithToast;
use App\Models\AttendanceSetting;
use App\Models\Holiday;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AttendanceSettingsManager extends Component
{
    use InteractsWithToast;

    public $settings = [];

    public $workingDays = [];

    public $showSuccessMessage = false;

    public $holidays = [];

    public $holidayId = null;

    public $holidayDate = '';

    public $holidayName = '';

    public $holidayType = 'national';

    public $holidayIsRecurring = false;

    public $holidayDescription = '';

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
        $this->loadHolidays();
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
        if (! empty($this->workingDays)) {
            AttendanceSetting::where('key', 'working_days')
                ->update(['value' => implode(',', $this->workingDays)]);
        }

        // Clear cache
        AttendanceSetting::clearCache();

        $this->flashToast('success', 'Pengaturan absensi berhasil disimpan!');

        $this->dispatch('settings-saved');
    }

    public function loadHolidays(): void
    {
        $this->holidays = Holiday::query()
            ->orderBy('date')
            ->get()
            ->map(function (Holiday $holiday) {
                return [
                    'id' => $holiday->id,
                    'date' => $holiday->date?->format('Y-m-d'),
                    'name' => $holiday->name,
                    'type' => $holiday->type,
                    'is_recurring' => (bool) $holiday->is_recurring,
                    'description' => $holiday->description,
                ];
            })
            ->toArray();
    }

    public function editHoliday(int $id): void
    {
        $holiday = Holiday::find($id);
        if (! $holiday) {
            $this->flashToast('error', 'Data libur tidak ditemukan.');

            return;
        }

        $this->holidayId = $holiday->id;
        $this->holidayDate = $holiday->date?->format('Y-m-d') ?? '';
        $this->holidayName = $holiday->name;
        $this->holidayType = $holiday->type;
        $this->holidayIsRecurring = (bool) $holiday->is_recurring;
        $this->holidayDescription = $holiday->description ?? '';
    }

    public function resetHolidayForm(): void
    {
        $this->holidayId = null;
        $this->holidayDate = '';
        $this->holidayName = '';
        $this->holidayType = 'national';
        $this->holidayIsRecurring = false;
        $this->holidayDescription = '';
        $this->resetErrorBag();
    }

    public function saveHoliday(): void
    {
        $validated = $this->validate([
            'holidayDate' => 'required|date',
            'holidayName' => 'required|string|max:255',
            'holidayType' => 'required|in:national,company,optional',
            'holidayIsRecurring' => 'boolean',
            'holidayDescription' => 'nullable|string|max:500',
        ]);

        if ($this->holidayId) {
            $holiday = Holiday::find($this->holidayId);
            if (! $holiday) {
                $this->flashToast('error', 'Data libur tidak ditemukan.');

                return;
            }

            $holiday->update([
                'date' => $validated['holidayDate'],
                'name' => $validated['holidayName'],
                'type' => $validated['holidayType'],
                'is_recurring' => (bool) $validated['holidayIsRecurring'],
                'description' => $validated['holidayDescription'] ?? null,
            ]);

            $this->flashToast('success', 'Tanggal libur berhasil diperbarui.');
        } else {
            Holiday::create([
                'date' => $validated['holidayDate'],
                'name' => $validated['holidayName'],
                'type' => $validated['holidayType'],
                'is_recurring' => (bool) $validated['holidayIsRecurring'],
                'description' => $validated['holidayDescription'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->flashToast('success', 'Tanggal libur berhasil ditambahkan.');
        }

        $this->loadHolidays();
        $this->resetHolidayForm();
    }

    public function deleteHoliday(int $id): void
    {
        $holiday = Holiday::find($id);
        if (! $holiday) {
            $this->flashToast('error', 'Data libur tidak ditemukan.');

            return;
        }

        $holiday->delete();
        $this->loadHolidays();

        if ($this->holidayId === $id) {
            $this->resetHolidayForm();
        }

        $this->flashToast('success', 'Tanggal libur berhasil dihapus.');
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
