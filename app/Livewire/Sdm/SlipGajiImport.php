<?php

namespace App\Livewire\Sdm;

use App\Models\SlipGajiHeader;
use App\Imports\SlipGajiImport as SlipGajiImportClass;
use App\Services\SlipGajiService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            'tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
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
            'tahun.max' => 'Tahun maksimal ' . (date('Y') + 1),
            'mode.required' => 'Mode slip gaji harus dipilih', // New
            'mode.in' => 'Mode slip gaji tidak valid', // New
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
        ];
    }

    public function mount()
    {
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
        $this->validate();

        try {
            $this->isUploading = true;
            $this->uploadProgress = 0;
            $this->uploadMessage = 'Memproses file...';

            // Check if periode and tahun combination already exists
            $existingHeader = SlipGajiHeader::where('periode', $this->periode)
                ->where('tahun', $this->tahun)
                ->first();

            if ($existingHeader) {
                throw new \Exception('Data slip gaji untuk periode ' . $this->periode . ' tahun ' . $this->tahun . ' sudah ada');
            }

            // Store the file
            $fileName = 'slip_gaji_' . $this->periode . '_' . $this->tahun . '_' . date('Y-m-d_His') . '.' . $this->fileUpload->getClientOriginalExtension();
            $filePath = $this->fileUpload->storeAs('private/slip-gaji', $fileName);

            $this->uploadProgress = 25;
            $this->uploadMessage = 'Mengimpor data...';

            // Import the data
            $import = new SlipGajiImportClass();
            Excel::import($import, $filePath);

            $this->uploadProgress = 75;
            $this->uploadMessage = 'Menyimpan data...';

            // Create header record
            $header = SlipGajiHeader::create([
                'periode' => $this->periode,
                'tahun' => $this->tahun,
                'mode' => $this->mode, // New: include mode
                'total_data' => $import->getRowCount() ?? 0,
                'status' => 'draft',
                'file_original' => $this->fileUpload->getClientOriginalName(),
                'file_path' => $filePath,
                'keterangan' => $this->keterangan,
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
            ]);

            $this->uploadProgress = 100;
            $this->uploadMessage = 'Upload berhasil!';

            // Reset form and close modal
            $this->closeModal();

            // Refresh the parent component
            $this->dispatch('slipGajiUploaded');

        } catch (\Exception $e) {
            Log::error('Slip Gaji upload error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $this->fileUpload ? $this->fileUpload->getClientOriginalName() : null,
                'periode' => $this->periode,
                'tahun' => $this->tahun,
            ]);

            $this->uploadMessage = 'Error: ' . $e->getMessage();
        } finally {
            $this->isUploading = false;
        }
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