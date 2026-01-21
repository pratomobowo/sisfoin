<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id_pegawai',
        'nip',
        'nip_pns',
        'nidn',
        'nup',
        'nidk',
        'nupn',
        'nik',
        'nama',
        'gelar_depan',
        'gelar_belakang',
        'jenis_kelamin',
        'id_agama',
        'agama',
        'id_kewarganegaraan',
        'kewarganegaraan',
        'tanggal_lahir',
        'tempat_lahir',
        'status_nikah',
        'alamat_domisili',
        'rt_domisili',
        'rw_domisili',
        'kode_pos_domisili',
        'id_kecamatan_domisili',
        'kecamatan_domisili',
        'id_kota_domisili',
        'kota_domisili',
        'id_provinsi_domisili',
        'provinsi_domisili',
        'alamat_ktp',
        'rt_ktp',
        'rw_ktp',
        'kode_pos_ktp',
        'id_kecamatan_ktp',
        'kecamatan_ktp',
        'id_kota_ktp',
        'kota_ktp',
        'id_provinsi_ktp',
        'provinsi_ktp',
        'nomor_hp',
        'email',
        'email_kampus',
        'id_satuan_kerja',
        'satuan_kerja',
        'id_home_base',
        'home_base',
        'telepon',
        'telepon_kantor',
        'telepon_alternatif',
        'id_pendidikan_terakhir',
        'tanggal_masuk',
        'tanggal_sertifikasi_dosen',
        'id_status_aktif',
        'status_aktif',
        'id_status_kepegawaian',
        'status_kepegawaian',
        'id_pangkat',
        'id_jabatan_fungsional',
        'jabatan_fungsional',
        'id_jabatan_sub_fungsional',
        'jabatan_sub_fungsional',
        'id_jabatan_struktural',
        'jabatan_struktural',
        'is_deleted',
        'id_sso',
        'last_sync_at',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    // Accessor untuk nama lengkap dengan gelar
    public function getNamaLengkapWithGelarAttribute()
    {
        $nama = '';
        if ($this->gelar_depan) {
            $nama .= $this->gelar_depan.' ';
        }
        $nama .= $this->nama;
        if ($this->gelar_belakang) {
            $nama .= ', '.$this->gelar_belakang;
        }

        return $nama;
    }
    
    // Accessor untuk nama lengkap (kompatibilitas)
    public function getNamaLengkapAttribute()
    {
        return $this->nama;
    }

    // Accessor untuk jenis kelamin
    public function getJenisKelaminTextAttribute()
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    // Accessor untuk status perkawinan
    public function getStatusPerkawinanTextAttribute()
    {
        $status = [
            'S' => 'Single',
            'M' => 'Menikah',
            'D' => 'Duda/Janda',
            'J' => 'Janda',
        ];

        return $status[$this->status_nikah] ?? $this->status_nikah;
    }

    // Scope untuk filter berdasarkan status aktif
    public function scopeActive($query)
    {
        return $query->where('status_aktif', 'Aktif');
    }

    // Scope untuk filter berdasarkan unit kerja
    public function scopeByUnitKerja($query, $unitKerja)
    {
        return $query->where('satuan_kerja', $unitKerja);
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', '%'.$search.'%')
                ->orWhere('nip', 'like', '%'.$search.'%')
                ->orWhere('nik', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('satuan_kerja', 'like', '%'.$search.'%');
        });
    }
}