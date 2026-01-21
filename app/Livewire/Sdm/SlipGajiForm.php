<?php

namespace App\Livewire\Sdm;

use App\Models\SlipGajiHeader;
use App\Services\SlipGajiService;
use Livewire\Component;
use Illuminate\Validation\Rule;

class SlipGajiForm extends Component
{
    public $slip_gaji_id;
    public $periode;
    public $tahun;
    public $status = 'draft';
    public $keterangan;
    public $editMode = false;
    public $showModal = false;

    protected $listeners = [
        'openSlipGajiForm' => 'openEditModal',
        'openSlipGajiCreateForm' => 'openCreateModal',
        'closeSlipGajiForm' => 'closeModal',
    ];

    private $slipGajiService;

    public function boot()
    {
        $this->slipGajiService = app(SlipGajiService::class);
    }

    protected function rules()
    {
        $rules = [
            'periode' => 'required|string|max:20',
            'tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'status' => 'required|in:draft,published',
            'keterangan' => 'nullable|string|max:500',
        ];

        if (!$this->editMode) {
            $rules['periode'][] = Rule::unique('slip_gaji_headers', 'periode')
                ->where('tahun', $this->tahun);
        } else {
            $rules['periode'][] = Rule::unique('slip_gaji_headers', 'periode')
                ->where('tahun', $this->tahun)
                ->ignore($this->slip_gaji_id);
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'periode.required' => 'Periode harus diisi',
            'periode.unique' => 'Periode untuk tahun ini sudah ada',
            'tahun.required' => 'Tahun harus diisi',
            'tahun.integer' => 'Tahun harus berupa angka',
            'tahun.min' => 'Tahun minimal 2020',
            'tahun.max' => 'Tahun maksimal ' . (date('Y') + 1),
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status harus draft atau published',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
        ];
    }

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.sdm.slip-gaji-form');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $this->editMode = true;

        try {
            $slipGaji = SlipGajiHeader::findOrFail($id);
            $this->slip_gaji_id = $slipGaji->id;
            $this->periode = $slipGaji->periode;
            $this->tahun = $slipGaji->tahun;
            $this->status = $slipGaji->status;
            $this->keterangan = $slipGaji->keterangan;

            $this->showModal = true;
        } catch (\Exception $e) {
            $this->showModal = false;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'periode' => $this->periode,
                'tahun' => $this->tahun,
                'status' => $this->status,
                'keterangan' => $this->keterangan,
            ];

            if ($this->editMode) {
                $this->slipGajiService->updateHeader($this->slip_gaji_id, $data);
            } else {
                $this->slipGajiService->createHeader($data);
            }

            $this->closeModal();
            $this->dispatch('slipGajiSaved');
        } catch (\Exception $e) {
            // Error logged, parent handles notification
        }
    }

    public function resetForm()
    {
        $this->slip_gaji_id = null;
        $this->periode = '';
        $this->tahun = date('Y');
        $this->status = 'draft';
        $this->keterangan = '';
        $this->resetValidation();
    }

    public function updatedTahun()
    {
        $this->validateOnly('tahun');
    }

    public function updatedPeriode()
    {
        $this->validateOnly('periode');
    }
}