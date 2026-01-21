<?php

namespace App\Http\Livewire\Superadmin;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $unitKerja = '';

    public $statusAktif = '';

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $employeeToDelete = null;

    // Form fields
    public $employee_id;

    public $nik;

    public $nama_lengkap;

    public $nama_panggilan;

    public $gelar_depan;

    public $gelar_belakang;

    public $jenis_kelamin;

    public $tanggal_lahir;

    public $tempat_lahir;

    public $agama;

    public $status_perkawinan;

    public $kewarganegaraan;

    public $golongan_darah;

    public $alamat_ktp;

    public $rt_ktp;

    public $rw_ktp;

    public $kelurahan_ktp;

    public $kecamatan_ktp;

    public $kabupaten_ktp;

    public $provinsi_ktp;

    public $kode_pos_ktp;

    public $alamat_domisili;

    public $rt_domisili;

    public $rw_domisili;

    public $kelurahan_domisili;

    public $kecamatan_domisili;

    public $kabupaten_domisili;

    public $provinsi_domisili;

    public $kode_pos_domisili;

    public $telepon;

    public $hp;

    public $email;

    public $email_kampus;

    public $nip;

    public $status_kepegawaian;

    public $jenis_pegawai;

    public $tanggal_masuk;

    public $tanggal_keluar;

    public $status_aktif;

    public $pangkat;

    public $jabatan_fungsional;

    public $jabatan_struktural;

    public $unit_kerja;

    public $fakultas;

    public $prodi;

    public $pendidikan_terakhir;

    public $jurusan;

    public $universitas;

    public $tahun_lulus;

    public $nama_bank;

    public $nomor_rekening;

    public $nama_rekening;

    public $bpjs_kesehatan;

    public $bpjs_ketenagakerjaan;

    public $npwp;

    public $status_pajak;

    protected function rules()
    {
        return [
            'nik' => 'required|string|max:20|unique:employees,nik,'.($this->employee_id ?? ''),
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:100',
            'agama' => 'required|string|max:50',
            'status_perkawinan' => 'required|in:S,M,D,J',
            'kewarganegaraan' => 'required|string|max:50',
            'golongan_darah' => 'nullable|string|max:5',
            'alamat_ktp' => 'required|string|max:255',
            'rt_ktp' => 'nullable|string|max:5',
            'rw_ktp' => 'nullable|string|max:5',
            'kelurahan_ktp' => 'required|string|max:100',
            'kecamatan_ktp' => 'required|string|max:100',
            'kabupaten_ktp' => 'required|string|max:100',
            'provinsi_ktp' => 'required|string|max:100',
            'kode_pos_ktp' => 'required|string|max:10',
            'alamat_domisili' => 'nullable|string|max:255',
            'rt_domisili' => 'nullable|string|max:5',
            'rw_domisili' => 'nullable|string|max:5',
            'kelurahan_domisili' => 'nullable|string|max:100',
            'kecamatan_domisili' => 'nullable|string|max:100',
            'kabupaten_domisili' => 'nullable|string|max:100',
            'provinsi_domisili' => 'nullable|string|max:100',
            'kode_pos_domisili' => 'nullable|string|max:10',
            'telepon' => 'nullable|string|max:20',
            'hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'email_kampus' => 'nullable|email|max:255',
            'nip' => 'nullable|string|max:50',
            'status_kepegawaian' => 'nullable|string|max:50',
            'jenis_pegawai' => 'nullable|string|max:50',
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date|after_or_equal:tanggal_masuk',
            'status_aktif' => 'required|in:Aktif,Tidak Aktif',
            'pangkat' => 'nullable|string|max:100',
            'jabatan_fungsional' => 'nullable|string|max:100',
            'jabatan_struktural' => 'nullable|string|max:100',
            'unit_kerja' => 'required|string|max:100',
            'fakultas' => 'nullable|string|max:100',
            'prodi' => 'nullable|string|max:100',
            'pendidikan_terakhir' => 'nullable|string|max:100',
            'jurusan' => 'nullable|string|max:100',
            'universitas' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1900|max:'.(date('Y') + 5),
            'nama_bank' => 'nullable|string|max:100',
            'nomor_rekening' => 'nullable|string|max:50',
            'nama_rekening' => 'nullable|string|max:255',
            'bpjs_kesehatan' => 'nullable|string|max:50',
            'bpjs_ketenagakerjaan' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'status_pajak' => 'nullable|string|max:50',
        ];
    }

    public function render()
    {
        $employees = Employee::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_lengkap', 'like', '%'.$this->search.'%')
                        ->orWhere('nip', 'like', '%'.$this->search.'%')
                        ->orWhere('nik', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->unitKerja, function ($query) {
                $query->where('unit_kerja', $this->unitKerja);
            })
            ->when($this->statusAktif, function ($query) {
                $query->where('status_aktif', $this->statusAktif);
            })
            ->latest()
            ->paginate(10);

        $unitKerjaOptions = Employee::distinct()->pluck('unit_kerja')->sort()->values();
        $statusOptions = ['Aktif', 'Tidak Aktif'];

        return view('livewire.sdm.employee-management', [
            'employees' => $employees,
            'unitKerjaOptions' => $unitKerjaOptions,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->showEditModal = true;
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $this->employee_id = $employee->id;
        $this->nik = $employee->nik;
        $this->nama_lengkap = $employee->nama_lengkap;
        $this->nama_panggilan = $employee->nama_panggilan;
        $this->gelar_depan = $employee->gelar_depan;
        $this->gelar_belakang = $employee->gelar_belakang;
        $this->jenis_kelamin = $employee->jenis_kelamin;
        $this->tanggal_lahir = $employee->tanggal_lahir->format('Y-m-d');
        $this->tempat_lahir = $employee->tempat_lahir;
        $this->agama = $employee->agama;
        $this->status_perkawinan = $employee->status_perkawinan;
        $this->kewarganegaraan = $employee->kewarganegaraan;
        $this->golongan_darah = $employee->golongan_darah;
        $this->alamat_ktp = $employee->alamat_ktp;
        $this->rt_ktp = $employee->rt_ktp;
        $this->rw_ktp = $employee->rw_ktp;
        $this->kelurahan_ktp = $employee->kelurahan_ktp;
        $this->kecamatan_ktp = $employee->kecamatan_ktp;
        $this->kabupaten_ktp = $employee->kabupaten_ktp;
        $this->provinsi_ktp = $employee->provinsi_ktp;
        $this->kode_pos_ktp = $employee->kode_pos_ktp;
        $this->alamat_domisili = $employee->alamat_domisili;
        $this->rt_domisili = $employee->rt_domisili;
        $this->rw_domisili = $employee->rw_domisili;
        $this->kelurahan_domisili = $employee->kelurahan_domisili;
        $this->kecamatan_domisili = $employee->kecamatan_domisili;
        $this->kabupaten_domisili = $employee->kabupaten_domisili;
        $this->provinsi_domisili = $employee->provinsi_domisili;
        $this->kode_pos_domisili = $employee->kode_pos_domisili;
        $this->telepon = $employee->telepon;
        $this->hp = $employee->hp;
        $this->email = $employee->email;
        $this->email_kampus = $employee->email_kampus;
        $this->nip = $employee->nip;
        $this->status_kepegawaian = $employee->status_kepegawaian;
        $this->jenis_pegawai = $employee->jenis_pegawai;
        $this->tanggal_masuk = $employee->tanggal_masuk->format('Y-m-d');
        $this->tanggal_keluar = $employee->tanggal_keluar ? $employee->tanggal_keluar->format('Y-m-d') : null;
        $this->status_aktif = $employee->status_aktif;
        $this->pangkat = $employee->pangkat;
        $this->jabatan_fungsional = $employee->jabatan_fungsional;
        $this->jabatan_struktural = $employee->jabatan_struktural;
        $this->unit_kerja = $employee->unit_kerja;
        $this->fakultas = $employee->fakultas;
        $this->prodi = $employee->prodi;
        $this->pendidikan_terakhir = $employee->pendidikan_terakhir;
        $this->jurusan = $employee->jurusan;
        $this->universitas = $employee->universitas;
        $this->tahun_lulus = $employee->tahun_lulus;
        $this->nama_bank = $employee->nama_bank;
        $this->nomor_rekening = $employee->nomor_rekening;
        $this->nama_rekening = $employee->nama_rekening;
        $this->bpjs_kesehatan = $employee->bpjs_kesehatan;
        $this->bpjs_ketenagakerjaan = $employee->bpjs_ketenagakerjaan;
        $this->npwp = $employee->npwp;
        $this->status_pajak = $employee->status_pajak;

        $this->showEditModal = true;
    }

    public function update()
    {
        $validatedData = $this->validate();

        if (isset($this->employee_id)) {
            // Update existing employee
            $employee = Employee::findOrFail($this->employee_id);
            $employee->update($validatedData);
            session()->flash('message', 'Employee updated successfully.');
        } else {
            // Create new employee
            Employee::create($validatedData);
            session()->flash('message', 'Employee created successfully.');
        }

        $this->closeModal();
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->employeeToDelete = Employee::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $employee = Employee::findOrFail($this->employeeToDelete->id);
        $employee->delete();
        $this->closeDeleteModal();
        session()->flash('message', 'Employee deleted successfully.');
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->resetInputFields();
    }

    public function resetInputFields()
    {
        $this->employee_id = null;
        $this->nik = '';
        $this->nama_lengkap = '';
        $this->nama_panggilan = '';
        $this->gelar_depan = '';
        $this->gelar_belakang = '';
        $this->jenis_kelamin = '';
        $this->tanggal_lahir = '';
        $this->tempat_lahir = '';
        $this->agama = '';
        $this->status_perkawinan = '';
        $this->kewarganegaraan = '';
        $this->golongan_darah = '';
        $this->alamat_ktp = '';
        $this->rt_ktp = '';
        $this->rw_ktp = '';
        $this->kelurahan_ktp = '';
        $this->kecamatan_ktp = '';
        $this->kabupaten_ktp = '';
        $this->provinsi_ktp = '';
        $this->kode_pos_ktp = '';
        $this->alamat_domisili = '';
        $this->rt_domisili = '';
        $this->rw_domisili = '';
        $this->kelurahan_domisili = '';
        $this->kecamatan_domisili = '';
        $this->kabupaten_domisili = '';
        $this->provinsi_domisili = '';
        $this->kode_pos_domisili = '';
        $this->telepon = '';
        $this->hp = '';
        $this->email = '';
        $this->email_kampus = '';
        $this->nip = '';
        $this->status_kepegawaian = '';
        $this->jenis_pegawai = '';
        $this->tanggal_masuk = '';
        $this->tanggal_keluar = '';
        $this->status_aktif = '';
        $this->pangkat = '';
        $this->jabatan_fungsional = '';
        $this->jabatan_struktural = '';
        $this->unit_kerja = '';
        $this->fakultas = '';
        $this->prodi = '';
        $this->pendidikan_terakhir = '';
        $this->jurusan = '';
        $this->universitas = '';
        $this->tahun_lulus = '';
        $this->nama_bank = '';
        $this->nomor_rekening = '';
        $this->nama_rekening = '';
        $this->bpjs_kesehatan = '';
        $this->bpjs_ketenagakerjaan = '';
        $this->npwp = '';
        $this->status_pajak = '';
    }
}
