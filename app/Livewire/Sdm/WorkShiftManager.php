<?php

namespace App\Livewire\Sdm;

use App\Livewire\Concerns\InteractsWithToast;
use App\Models\WorkShift;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class WorkShiftManager extends Component
{
    use InteractsWithToast, WithPagination;

    public $showModal = false;

    public $editingId = null;

    public $name = '';

    public $code = '';

    public $start_time = '08:00';

    public $end_time = '14:00';

    public $early_arrival_threshold = '07:40';

    public $late_tolerance_minutes = 5;

    public $work_hours = 6;

    public $color = 'blue';

    public $is_default = false;

    public $is_active = true;

    public $description = '';

    public $colors = [
        'blue' => 'Biru',
        'green' => 'Hijau',
        'yellow' => 'Kuning',
        'red' => 'Merah',
        'purple' => 'Ungu',
        'pink' => 'Pink',
        'indigo' => 'Indigo',
        'gray' => 'Abu-abu',
    ];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('work_shifts', 'code')->ignore($this->editingId),
            ],
            'start_time' => 'required',
            'end_time' => 'required',
            'early_arrival_threshold' => 'required',
            'late_tolerance_minutes' => 'required|integer|min:0',
            'work_hours' => 'required|numeric|min:0',
            'color' => 'required',
            'is_active' => 'boolean',
        ];
    }

    public function openModal($id = null)
    {
        $this->resetValidation();

        if ($id) {
            $shift = WorkShift::findOrFail($id);
            $this->editingId = $id;
            $this->name = $shift->name;
            $this->code = $shift->code;
            $this->start_time = substr($shift->start_time, 0, 5);
            $this->end_time = substr($shift->end_time, 0, 5);
            $this->early_arrival_threshold = substr($shift->early_arrival_threshold, 0, 5);
            $this->late_tolerance_minutes = $shift->late_tolerance_minutes;
            $this->work_hours = $shift->work_hours;
            $this->color = $shift->color;
            $this->is_default = $shift->is_default;
            $this->is_active = $shift->is_active;
            $this->description = $shift->description;
        } else {
            $this->reset(['editingId', 'name', 'code', 'description']);
            $this->start_time = '08:00';
            $this->end_time = '14:00';
            $this->early_arrival_threshold = '07:40';
            $this->late_tolerance_minutes = 5;
            $this->work_hours = 6;
            $this->color = 'blue';
            $this->is_default = false;
            $this->is_active = true;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingId = null;
    }

    public function save()
    {
        abort_unless(auth()->user()?->can('employee.attendance.edit'), 403);

        $this->code = strtoupper(trim((string) $this->code));
        $this->validate();

        if ($this->editingId && ! $this->isEditAllowed()) {
            $this->toastError('Shift sudah dipakai pada jadwal karyawan. Buat shift baru untuk mengubah jam/kode agar histori absensi tetap aman.');

            return;
        }

        // If setting as default, unset other defaults
        if ($this->is_default) {
            WorkShift::where('is_default', true)->update(['is_default' => false]);
        }

        $data = [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'start_time' => $this->start_time.':00',
            'end_time' => $this->end_time.':00',
            'early_arrival_threshold' => $this->early_arrival_threshold.':00',
            'late_tolerance_minutes' => $this->late_tolerance_minutes,
            'work_hours' => $this->work_hours,
            'color' => $this->color,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'description' => $this->description,
        ];

        if ($this->editingId) {
            WorkShift::find($this->editingId)->update($data);
        } else {
            WorkShift::create($data);
        }

        $this->closeModal();
        $this->toastSuccess('Shift berhasil disimpan!');
    }

    public function delete($id)
    {
        abort_unless(auth()->user()?->can('employee.attendance.edit'), 403);

        $shift = WorkShift::withCount('employeeAssignments')->find($id);

        if (! $shift) {
            $this->toastError('Shift tidak ditemukan!');

            return;
        }

        if ($shift->is_default) {
            $this->toastError('Shift default tidak dapat dihapus!');

            return;
        }

        if ($shift->employee_assignments_count > 0) {
            $this->toastError('Shift sudah dipakai pada jadwal karyawan. Nonaktifkan shift jika tidak ingin dipakai lagi.');

            return;
        }

        $shift->delete();
        $this->toastSuccess('Shift berhasil dihapus!');
    }

    private function isEditAllowed(): bool
    {
        $shift = WorkShift::withCount('employeeAssignments')->find($this->editingId);

        if (! $shift) {
            return false;
        }

        if ($shift->employee_assignments_count === 0) {
            return true;
        }

        $immutableFields = [
            'code' => $this->code,
            'start_time' => $this->start_time.':00',
            'end_time' => $this->end_time.':00',
            'early_arrival_threshold' => $this->early_arrival_threshold.':00',
            'late_tolerance_minutes' => (int) $this->late_tolerance_minutes,
            'work_hours' => (float) $this->work_hours,
        ];

        foreach ($immutableFields as $field => $value) {
            if ($field === 'work_hours') {
                if ((float) $shift->{$field} !== $value) {
                    return false;
                }

                continue;
            }

            if ($shift->{$field} != $value) {
                return false;
            }
        }

        return true;
    }

    public function setAsDefault($id)
    {
        $target = WorkShift::find($id);

        if (! $target) {
            $this->toastError('Shift tidak ditemukan!');

            return;
        }

        if (! $target->is_active) {
            $this->toastError('Shift nonaktif tidak dapat dijadikan default!');

            return;
        }

        WorkShift::where('is_default', true)->update(['is_default' => false]);
        $target->update(['is_default' => true]);
        $this->toastSuccess('Shift berhasil diset sebagai default!');
    }

    public function render()
    {
        $shifts = WorkShift::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('livewire.sdm.work-shift-manager', [
            'shifts' => $shifts,
        ]);
    }
}
