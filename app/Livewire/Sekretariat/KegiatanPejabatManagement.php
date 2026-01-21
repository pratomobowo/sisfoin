<?php

namespace App\Livewire\Sekretariat;

use App\Models\KegiatanPejabat;
use App\Models\Dosen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class KegiatanPejabatManagement extends Component
{
    use WithFileUploads, WithPagination;

    // Properties for data
    public $kegiatanId;
    public $namaKegiatan = '';
    public $jenisKegiatan = '';
    public $tempatKegiatan = '';
    public $tanggalMulai = '';
    public $tanggalSelesai = '';
    public $pejabatTerkait = [];
    public $disposisiKepada = '';
    public $keterangan = '';
    public $file = null;

    // Properties for view
    public $viewKegiatan;

    // Properties for filters
    public $search = '';
    public $filterJenis = '';
    public $filterPejabat = '';
    public $perPage = 10;

    // Properties for modals
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;

    // Delete properties
    public $kegiatanToDelete = null;

    // Properties for dropdowns
    public $pejabatList = [];
    public $jenisKegiatanOptions = [];

    // Validation rules
    protected $rules = [
        'namaKegiatan' => 'required|string|max:255',
        'jenisKegiatan' => 'required|string|max:100',
        'tempatKegiatan' => 'required|string|max:255',
        'tanggalMulai' => 'required|date',
        'tanggalSelesai' => 'required|date|after_or_equal:tanggalMulai',
        'pejabatTerkait' => 'required|array|min:1',
        'disposisiKepada' => 'nullable|string|max:255',
        'keterangan' => 'nullable|string',
        'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
    ];

    protected $messages = [
        'namaKegiatan.required' => 'Nama kegiatan wajib diisi',
        'jenisKegiatan.required' => 'Jenis kegiatan wajib dipilih',
        'tempatKegiatan.required' => 'Tempat kegiatan wajib diisi',
        'tanggalMulai.required' => 'Tanggal mulai wajib diisi',
        'tanggalSelesai.required' => 'Tanggal selesai wajib diisi',
        'tanggalSelesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai',
        'pejabatTerkait.required' => 'Minimal satu pejabat harus dipilih',
        'pejabatTerkait.min' => 'Minimal satu pejabat harus dipilih',
        'file.mimes' => 'File harus berformat PDF, DOC, DOCX, JPG, JPEG, atau PNG',
        'file.max' => 'Ukuran file maksimal 10MB',
    ];

    public function mount()
    {
        $this->loadDropdownData();
    }

    public function loadDropdownData()
    {
        // Load pejabat (rectors) from dosens
        $this->pejabatList = Dosen::where(function ($query) {
            $query->where('jabatan_struktural', 'like', '%rektor%')
                ->orWhere('jabatan_struktural', 'like', '%Rektor%');
        })
        ->where('status_aktif', 'Aktif')
        ->orderBy('nama')
        ->get()
        ->map(function ($dosen) {
            return [
                'id' => $dosen->id,
                'nama' => $dosen->nama_lengkap_with_gelar,
                'jabatan' => $dosen->jabatan_struktural,
                'display' => $dosen->nama_lengkap_with_gelar . ' - ' . $dosen->jabatan_struktural,
            ];
        })
        ->toArray();

        // Load jenis kegiatan options
        $this->jenisKegiatanOptions = [
            'Rapat Internal',
            'Rapat Eksternal',
            'Kunjungan Kerja',
            'Upacara',
            'Seminar',
            'Workshop',
            'Pelatihan',
            'Lain-lain'
        ];
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
            $data = [
                'nama_kegiatan' => $this->namaKegiatan,
                'jenis_kegiatan' => $this->jenisKegiatan,
                'tempat_kegiatan' => $this->tempatKegiatan,
                'tanggal_mulai' => $this->tanggalMulai,
                'tanggal_selesai' => $this->tanggalSelesai,
                'pejabat_terkait' => $this->pejabatTerkait,
                'disposisi_kepada' => $this->disposisiKepada,
                'keterangan' => $this->keterangan,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];

            // Handle file upload
            if ($this->file) {
                $fileName = $this->file->getClientOriginalName();
                $fileSize = $this->file->getSize();
                $filePath = $this->file->store('kegiatan-pejabat', 'public');

                $data['file_lampiran'] = $filePath;
                $data['file_name'] = $fileName;
                $data['file_size'] = $fileSize;
            }

            // Create kegiatan pejabat
            KegiatanPejabat::create($data);

            DB::commit();

            $this->showCreateModal = false;
            $this->resetInputFields();
            $this->loadDropdownData();

            session()->flash('message', 'Kegiatan Pejabat berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = KegiatanPejabat::query()
            ->with(['createdBy', 'updatedBy'])
            ->search($this->search)
            ->filterByJenis($this->filterJenis)
            ->filterByPejabat($this->filterPejabat)
            ->latest();

        $kegiatanPejabat = $query->paginate($this->perPage);

        return view('livewire.sekretariat.kegiatan-pejabat-management', [
            'kegiatanPejabat' => $kegiatanPejabat,
        ]);
    }

    public function edit($id)
    {
        $kegiatan = KegiatanPejabat::findOrFail($id);

        $this->kegiatanId = $id;
        $this->namaKegiatan = $kegiatan->nama_kegiatan;
        $this->jenisKegiatan = $kegiatan->jenis_kegiatan;
        $this->tempatKegiatan = $kegiatan->tempat_kegiatan;
        $this->tanggalMulai = $kegiatan->tanggal_mulai->format('Y-m-d');
        $this->tanggalSelesai = $kegiatan->tanggal_selesai->format('Y-m-d');
        $this->pejabatTerkait = $kegiatan->pejabat_terkait ?? [];
        $this->disposisiKepada = $kegiatan->disposisi_kepada;
        $this->keterangan = $kegiatan->keterangan;

        $this->showEditModal = true;
    }

    public function update()
    {
        $rules = $this->rules;
        $rules['file'] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'; // File is optional when updating

        $this->validate($rules);

        DB::beginTransaction();
        try {
            $kegiatan = KegiatanPejabat::findOrFail($this->kegiatanId);

            $updateData = [
                'nama_kegiatan' => $this->namaKegiatan,
                'jenis_kegiatan' => $this->jenisKegiatan,
                'tempat_kegiatan' => $this->tempatKegiatan,
                'tanggal_mulai' => $this->tanggalMulai,
                'tanggal_selesai' => $this->tanggalSelesai,
                'pejabat_terkait' => $this->pejabatTerkait,
                'disposisi_kepada' => $this->disposisiKepada,
                'keterangan' => $this->keterangan,
                'updated_by' => auth()->id(),
            ];

            // Handle file upload if new file is provided
            if ($this->file) {
                // Delete old file
                if ($kegiatan->file_lampiran) {
                    Storage::disk('public')->delete($kegiatan->file_lampiran);
                }

                $fileName = $this->file->getClientOriginalName();
                $fileSize = $this->file->getSize();
                $filePath = $this->file->store('kegiatan-pejabat', 'public');

                $updateData['file_lampiran'] = $filePath;
                $updateData['file_name'] = $fileName;
                $updateData['file_size'] = $fileSize;
            }

            $kegiatan->update($updateData);

            DB::commit();

            $this->showEditModal = false;
            $this->resetInputFields();
            $this->loadDropdownData();

            session()->flash('message', 'Kegiatan Pejabat berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        $this->viewKegiatan = KegiatanPejabat::with(['createdBy', 'updatedBy'])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function delete($id)
    {
        $kegiatan = KegiatanPejabat::findOrFail($id);
        $kegiatan->delete();

        session()->flash('message', 'Kegiatan Pejabat berhasil dihapus');
    }

    public function download($id)
    {
        $kegiatan = KegiatanPejabat::findOrFail($id);

        if (!$kegiatan->file_lampiran || !Storage::disk('public')->exists($kegiatan->file_lampiran)) {
            session()->flash('error', 'File tidak ditemukan');
            return;
        }

        return Storage::disk('public')->download($kegiatan->file_lampiran, $kegiatan->file_name);
    }

    public function resetInputFields()
    {
        $this->kegiatanId = null;
        $this->namaKegiatan = '';
        $this->jenisKegiatan = '';
        $this->tempatKegiatan = '';
        $this->tanggalMulai = '';
        $this->tanggalSelesai = '';
        $this->pejabatTerkait = [];
        $this->disposisiKepada = '';
        $this->keterangan = '';
        $this->file = null;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterJenis = '';
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
        $this->viewKegiatan = null;
    }

    // Pagination methods
    public function previousPage()
    {
        $this->setPage(max(1, $this->page - 1));
    }

    public function nextPage()
    {
        $this->setPage(min($this->kegiatanPejabat->lastPage(), $this->page + 1));
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }
}
