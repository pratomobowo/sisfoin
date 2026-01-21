<?php

namespace App\Livewire\Sekretariat;

use App\Models\Dosen;
use App\Models\KategoriSk;
use App\Models\SuratKeputusan;
use App\Models\TipeSurat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SuratKeputusanManagement extends Component
{
    use WithFileUploads, WithPagination;

    // Properties for data
    public $suratKeputusanId;
    public $nomorSurat = '';
    public $tipeSurat = '';
    public $kategoriSk = '';
    public $tentang = '';
    public $tanggalPenetapan = '';
    public $tanggalBerlaku = '';
    public $ditandatanganiOleh = '';
    public $deskripsi = '';
    public $file = null;

    // Properties for view
    public $viewSuratKeputusan;

    // Properties for filters
    public $search = '';
    public $filterTipeSurat = '';
    public $filterKategoriSk = '';
    public $filterPejabat = '';
    public $perPage = 10;

    // Properties for modals
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;

    // Delete properties
    public $suratToDelete = null;

    // Properties for dropdowns
    public $tipeSuratList = [];

    public $kategoriSkList = [];

    public $pejabatList = [];

    // Validation rules
    protected $rules = [
        'nomorSurat' => 'required|string|max:50|unique:surat_keputusan,nomor_surat',
        'tipeSurat' => 'required|string|max:100',
        'kategoriSk' => 'required|string|max:100',
        'tentang' => 'required|string|max:255',
        'tanggalPenetapan' => 'required|date',
        'tanggalBerlaku' => 'required|date|after_or_equal:tanggalPenetapan',
        'ditandatanganiOleh' => 'required|string|max:100',
        'deskripsi' => 'nullable|string',
        'file' => 'nullable|file|mimes:pdf|max:20480', // 20MB max
    ];

    protected $messages = [
        'nomorSurat.required' => 'Nomor surat wajib diisi',
        'nomorSurat.unique' => 'Nomor surat sudah digunakan',
        'tipeSurat.required' => 'Tipe surat wajib diisi',
        'kategoriSk.required' => 'Kategori SK wajib diisi',
        'tentang.required' => 'Tentang/perihal wajib diisi',
        'tanggalPenetapan.required' => 'Tanggal penetapan wajib diisi',
        'tanggalBerlaku.required' => 'Tanggal berlaku wajib diisi',
        'tanggalBerlaku.after_or_equal' => 'Tanggal berlaku harus sama atau setelah tanggal penetapan',
        'ditandatanganiOleh.required' => 'Ditandatangani oleh wajib diisi',
        'file.required' => 'File PDF wajib diupload',
        'file.mimes' => 'File harus berformat PDF',
        'file.max' => 'Ukuran file maksimal 20MB',
    ];

    public function mount()
    {
        $this->loadDropdownData();
    }

    public function loadDropdownData()
    {
        $this->tipeSuratList = TipeSurat::getDropdownOptions();
        $this->kategoriSkList = KategoriSk::getDropdownOptions();

        // Load pejabat from dosens with structural position
        $this->pejabatList = Dosen::whereNotNull('jabatan_struktural')
            ->where('jabatan_struktural', '!=', '')
            ->orderBy('nama')
            ->get()
            ->map(function ($dosen) {
                return [
                    'id' => $dosen->id,
                    'nama' => $dosen->nama_lengkap_with_gelar,
                    'jabatan' => $dosen->jabatan_struktural,
                    'display' => $dosen->nama_lengkap_with_gelar.' - '.$dosen->jabatan_struktural,
                ];
            })
            ->toArray();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->showCreateModal = true;
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Handle file upload
            if (! $this->file) {
                session()->flash('error', 'File PDF wajib diupload');
                return;
            }

            $fileName = $this->file->getClientOriginalName();
            $fileSize = $this->file->getSize();
            $filePath = $this->file->store('surat-keputusan', 'public');

            // Create or get tipe surat
            TipeSurat::getOrCreate($this->tipeSurat, null, auth()->id());

            // Create or get kategori SK
            KategoriSk::getOrCreate($this->kategoriSk, null, auth()->id());

            // Prepare data for creation
            $data = [
                'nomor_surat' => $this->nomorSurat,
                'tipe_surat' => $this->tipeSurat,
                'kategori_sk' => $this->kategoriSk,
                'tentang' => $this->tentang,
                'tanggal_penetapan' => $this->tanggalPenetapan,
                'tanggal_berlaku' => $this->tanggalBerlaku,
                'ditandatangani_oleh' => $this->ditandatanganiOleh,
                'deskripsi' => $this->deskripsi,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'created_by' => auth()->id(),
            ];

            // Create surat keputusan
            SuratKeputusan::create($data);

            DB::commit();

            $this->showCreateModal = false;
            $this->resetInputFields();
            $this->loadDropdownData();

            session()->flash('message', 'Surat Keputusan berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function render()
    {
        $query = SuratKeputusan::query()
            ->with('createdBy')
            ->search($this->search)
            ->filterByTipeSurat($this->filterTipeSurat)
            ->filterByKategoriSk($this->filterKategoriSk)
            ->filterByPejabat($this->filterPejabat)
            ->latest();

        $suratKeputusan = $query->paginate($this->perPage);

        // Get unique values for filters
        $tipeSuratOptions = SuratKeputusan::distinct()->pluck('tipe_surat')->sort()->values();
        $kategoriSkOptions = SuratKeputusan::distinct()->pluck('kategori_sk')->sort()->values();
        $pejabatOptions = SuratKeputusan::distinct()->pluck('ditandatangani_oleh')->sort()->values();

        return view('livewire.sekretariat.surat-keputusan-management', [
            'suratKeputusan' => $suratKeputusan,
            'tipeSuratOptions' => $tipeSuratOptions,
            'kategoriSkOptions' => $kategoriSkOptions,
            'pejabatOptions' => $pejabatOptions,
        ]);
    }

    public function edit($id)
    {
        $suratKeputusan = SuratKeputusan::findOrFail($id);

        $this->suratKeputusanId = $id;
        $this->nomorSurat = $suratKeputusan->nomor_surat;
        $this->tipeSurat = $suratKeputusan->tipe_surat;
        $this->kategoriSk = $suratKeputusan->kategori_sk;
        $this->tentang = $suratKeputusan->tentang;
        $this->tanggalPenetapan = $suratKeputusan->tanggal_penetapan->format('Y-m-d');
        $this->tanggalBerlaku = $suratKeputusan->tanggal_berlaku->format('Y-m-d');
        $this->ditandatanganiOleh = $suratKeputusan->ditandatangani_oleh;
        $this->deskripsi = $suratKeputusan->deskripsi;

        $this->showEditModal = true;
    }

    public function update()
    {
        $rules = $this->rules;
        $rules['nomorSurat'] = 'required|string|max:50|unique:surat_keputusan,nomor_surat,'.$this->suratKeputusanId;
        $rules['file'] = 'nullable|file|mimes:pdf|max:20480'; // File is optional when updating

        $this->validate($rules);

        DB::beginTransaction();
        try {
            $suratKeputusan = SuratKeputusan::findOrFail($this->suratKeputusanId);

            $updateData = [
                'nomor_surat' => $this->nomorSurat,
                'tipe_surat' => $this->tipeSurat,
                'kategori_sk' => $this->kategoriSk,
                'tentang' => $this->tentang,
                'tanggal_penetapan' => $this->tanggalPenetapan,
                'tanggal_berlaku' => $this->tanggalBerlaku,
                'ditandatangani_oleh' => $this->ditandatanganiOleh,
                'deskripsi' => $this->deskripsi,
            ];

            // Handle file upload if new file is provided
            if ($this->file) {
                // Delete old file
                if ($suratKeputusan->file_path) {
                    Storage::disk('public')->delete($suratKeputusan->file_path);
                }

                $fileName = $this->file->getClientOriginalName();
                $fileSize = $this->file->getSize();
                $filePath = $this->file->store('surat-keputusan', 'public');

                $updateData['file_path'] = $filePath;
                $updateData['file_name'] = $fileName;
                $updateData['file_size'] = $fileSize;
            }

            // Create or get tipe surat
            TipeSurat::getOrCreate($this->tipeSurat, null, auth()->id());

            // Create or get kategori SK
            KategoriSk::getOrCreate($this->kategoriSk, null, auth()->id());

            $suratKeputusan->update($updateData);

            DB::commit();

            $this->showEditModal = false;
            $this->resetInputFields();
            $this->loadDropdownData();

            session()->flash('message', 'Surat Keputusan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function view($id)
    {
        $this->viewSuratKeputusan = SuratKeputusan::with('createdBy')->findOrFail($id);
        $this->showViewModal = true;
    }

    public function delete($id)
    {
        $suratKeputusan = SuratKeputusan::findOrFail($id);

        // Delete file from storage
        if ($suratKeputusan->file_path) {
            Storage::disk('public')->delete($suratKeputusan->file_path);
        }

        $suratKeputusan->delete();

        session()->flash('message', 'Surat Keputusan berhasil dihapus');
    }

    public function download($id)
    {
        $suratKeputusan = SuratKeputusan::findOrFail($id);

        if (! Storage::disk('public')->exists($suratKeputusan->file_path)) {
            session()->flash('error', 'File tidak ditemukan');

            return;
        }

        return Storage::disk('public')->download($suratKeputusan->file_path, $suratKeputusan->file_name);
    }

    public function resetInputFields()
    {
        $this->suratKeputusanId = null;
        $this->nomorSurat = '';
        $this->tipeSurat = '';
        $this->kategoriSk = '';
        $this->tentang = '';
        $this->tanggalPenetapan = '';
        $this->tanggalBerlaku = '';
        $this->ditandatanganiOleh = '';
        $this->deskripsi = '';
        $this->file = null;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterTipeSurat = '';
        $this->filterKategoriSk = '';
        $this->filterPejabat = '';
        $this->perPage = 10;
    }

    // Modal close methods
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetInputFields();
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetInputFields();
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewSuratKeputusan = null;
    }

    // Pagination methods
    public function previousPage()
    {
        $this->setPage(max(1, $this->page - 1));
    }

    public function nextPage()
    {
        $this->setPage(min($this->suratKeputusan->lastPage(), $this->page + 1));
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }
}
