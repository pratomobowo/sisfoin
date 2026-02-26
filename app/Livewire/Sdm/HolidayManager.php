<?php

namespace App\Livewire\Sdm;

use App\Livewire\Concerns\InteractsWithToast;
use App\Models\Holiday;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class HolidayManager extends Component
{
    use InteractsWithToast, WithPagination;

    public $showModal = false;

    public $editingId = null;

    public $date = '';

    public $name = '';

    public $type = 'national';

    public $is_recurring = false;

    public $description = '';

    public $search = '';

    public $filterYear = '';

    protected $rules = [
        'date' => 'required|date',
        'name' => 'required|string|max:255',
        'type' => 'required|in:national,company,optional',
        'is_recurring' => 'boolean',
        'description' => 'nullable|string',
    ];

    public function mount()
    {
        $this->filterYear = date('Y');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();

        if ($id) {
            $holiday = Holiday::findOrFail($id);
            $this->editingId = $id;
            $this->date = $holiday->date->format('Y-m-d');
            $this->name = $holiday->name;
            $this->type = $holiday->type;
            $this->is_recurring = $holiday->is_recurring;
            $this->description = $holiday->description;
        } else {
            $this->editingId = null;
            $this->date = '';
            $this->name = '';
            $this->type = 'national';
            $this->is_recurring = false;
            $this->description = '';
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
        $this->validate();

        $data = [
            'date' => $this->date,
            'name' => $this->name,
            'type' => $this->type,
            'is_recurring' => $this->is_recurring,
            'description' => $this->description,
            'created_by' => auth()->id(),
        ];

        if ($this->editingId) {
            Holiday::find($this->editingId)->update($data);
        } else {
            Holiday::create($data);
        }

        $this->closeModal();
        $this->toastSuccess('Hari libur berhasil disimpan!');
    }

    public function delete($id)
    {
        Holiday::destroy($id);
        $this->toastSuccess('Hari libur berhasil dihapus!');
    }

    public function getTypeLabels()
    {
        return [
            'national' => 'Nasional',
            'company' => 'Perusahaan',
            'optional' => 'Opsional',
        ];
    }

    public function render()
    {
        $holidays = Holiday::when($this->search, function ($query) {
            $query->where('name', 'like', '%'.$this->search.'%');
        })
            ->when($this->filterYear, function ($query) {
                $query->whereYear('date', $this->filterYear);
            })
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('livewire.sdm.holiday-manager', [
            'holidays' => $holidays,
            'typeLabels' => $this->getTypeLabels(),
        ]);
    }
}
