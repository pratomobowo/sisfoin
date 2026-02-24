<?php

namespace App\Livewire\Sdm;

use App\Services\SlipGajiService;
use Livewire\Component;
use Livewire\WithFileUploads;

class SlipGajiImport extends Component
{
    use WithFileUploads;

    public $showModal = false;

    public $fileUpload;

    public $periode;

    public $tahun;

    public $mode = 'standard'; // New: mode property

    public $keterangan;

    public $isUploading = false;

    public $uploadProgress = 0;

    public $uploadMessage = '';

    protected $listeners = [
        'openSlipGajiImport' => 'openModal',
        'closeSlipGajiImport' => 'closeModal',
    ];

    private $slipGajiService;

    public function boot()
    {
        $this->slipGajiService = app(SlipGajiService::class);
    }

    protected function rules()
    {
        return [
            'fileUpload' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
            'periode' => 'required|string|max:20',
            'tahun' => 'required|integer|min:2020|max:'.(date('Y') + 1),
            'mode' => 'required|in:standard,gaji_13', // New: mode validation
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    protected function messages()
    {
        return [
            'fileUpload.required' => 'File harus dipilih',
            'fileUpload.file' => 'File tidak valid',
            'fileUpload.mimes' => 'File harus berformat Excel (.xlsx atau .xls)',
            'fileUpload.max' => 'Ukuran file maksimal 10MB',
            'periode.required' => 'Periode harus diisi',
            'periode.max' => 'Periode maksimal 20 karakter',
            'tahun.required' => 'Tahun harus diisi',
            'tahun.integer' => 'Tahun harus berupa angka',
            'tahun.min' => 'Tahun minimal 2020',
            'tahun.max' => 'Tahun maksimal '.(date('Y') + 1),
            'mode.required' => 'Mode slip gaji harus dipilih', // New
            'mode.in' => 'Mode slip gaji tidak valid', // New
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
        ];
    }

    public function mount()
    {
        // Legacy component retained for backward compatibility, hidden from index flow.
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.sdm.slip-gaji-import');
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function uploadSlipGaji()
    {
        // Disable legacy upload flow until schema/services are fully aligned.
        $this->addError('legacy', 'Fitur import lama dinonaktifkan sementara. Gunakan menu Upload Data Slip Gaji pada header halaman.');
    }

    public function resetForm()
    {
        $this->fileUpload = null;
        $this->periode = '';
        $this->tahun = date('Y');
        $this->mode = 'standard'; // New: reset mode
        $this->keterangan = '';
        $this->isUploading = false;
        $this->uploadProgress = 0;
        $this->uploadMessage = '';
        $this->resetValidation();
    }

    public function updatedFileUpload()
    {
        $this->validateOnly('fileUpload');
    }

    public function updatedPeriode()
    {
        $this->validateOnly('periode');
    }

    public function updatedTahun()
    {
        $this->validateOnly('tahun');
    }
}
