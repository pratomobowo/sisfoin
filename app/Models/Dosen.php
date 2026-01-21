<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dosen extends Model
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
        'golongan_darah',
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
        'telepon',
        'telepon_kantor',
        'telepon_alternatif',
        'id_satuan_kerja',
        'satuan_kerja',
        'id_home_base',
        'home_base',
        'id_pendidikan_terakhir',
        'pendidikan_terakhir',
        'jurusan',
        'universitas',
        'tahun_lulus',
        'tanggal_masuk',
        'tanggal_keluar',
        'tanggal_sertifikasi_dosen',
        'id_status_aktif',
        'status_aktif',
        'id_status_kepegawaian',
        'status_kepegawaian',
        'jenis_pegawai',
        'id_pangkat',
        'pangkat',
        'id_jabatan_fungsional',
        'jabatan_fungsional',
        'id_jabatan_sub_fungsional',
        'jabatan_sub_fungsional',
        'id_jabatan_struktural',
        'jabatan_struktural',
        'nama_bank',
        'nomor_rekening',
        'nama_rekening',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'npwp',
        'status_pajak',
        'is_deleted',
        'id_sso',
        'api_created_at',
        'api_updated_at',
        'last_synced_at',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
        'tanggal_sertifikasi_dosen' => 'date',
        'api_created_at' => 'datetime',
        'api_updated_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_deleted' => 'boolean',
        'id_agama' => 'integer',
        'id_kecamatan_domisili' => 'integer',
        'id_kota_domisili' => 'integer',
        'id_provinsi_domisili' => 'integer',
        'id_kecamatan_ktp' => 'integer',
        'id_kota_ktp' => 'integer',
        'id_provinsi_ktp' => 'integer',
        'id_satuan_kerja' => 'integer',
        'id_home_base' => 'integer',
        'id_pendidikan_terakhir' => 'integer',
        'id_sso' => 'integer',
        'tahun_lulus' => 'integer',
    ];

    /**
     * Get the full name with titles
     */
    public function getNamaLengkapWithGelarAttribute()
    {
        $nama = $this->nama;

        if ($this->gelar_depan) {
            $nama = $this->gelar_depan.' '.$nama;
        }

        if ($this->gelar_belakang) {
            $nama = $nama.', '.$this->gelar_belakang;
        }

        return $nama;
    }

    /**
     * Get formatted status text with color class
     */
    public function getStatusTextAttribute()
    {
        return match ($this->status_aktif) {
            'AA', 'Aktif' => 'Aktif',
            'TA', 'Tidak Aktif' => 'Tidak Aktif',
            'M', 'Meninggal Dunia' => 'Meninggal Dunia',
            'PN', 'Pensiun Normal' => 'Pensiun Normal',
            'MD', 'Mengundurkan diri' => 'Mengundurkan diri',
            default => $this->status_aktif ?? 'Tidak Diketahui'
        };
    }

    /**
     * Get status color class for UI
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status_aktif) {
            'AA', 'Aktif' => 'bg-green-100 text-green-800',
            'TA', 'Tidak Aktif' => 'bg-red-100 text-red-800',
            'M', 'Meninggal Dunia' => 'bg-gray-100 text-gray-800',
            'PN', 'Pensiun Normal' => 'bg-blue-100 text-blue-800',
            'MD', 'Mengundurkan diri' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Scope for searching dosens
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%")
                ->orWhere('nidn', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('email_kampus', 'like', "%{$search}%")
                ->orWhere('satuan_kerja', 'like', "%{$search}%")
                ->orWhere('jabatan_fungsional', 'like', "%{$search}%")
                ->orWhere('jabatan_struktural', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for filtering by status
     */
    public function scopeFilterByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status_aktif', $status);
        }

        return $query;
    }

    /**
     * Scope for filtering by satuan kerja
     */
    public function scopeBySatuanKerja($query, $satuanKerja)
    {
        if ($satuanKerja) {
            return $query->where('satuan_kerja', $satuanKerja);
        }

        return $query;
    }

    /**
     * Legacy method for backward compatibility
     */
    public function scopeFilterBySatuanKerja($query, $satuanKerja)
    {
        return $this->scopeBySatuanKerja($query, $satuanKerja);
    }

    /**
     * Scope for filtering by jabatan fungsional
     */
    public function scopeFilterByJabatanFungsional($query, $jabatan)
    {
        if ($jabatan) {
            return $query->where('jabatan_fungsional', $jabatan);
        }

        return $query;
    }

    /**
     * Get distinct satuan kerja for filter options
     */
    public static function getDistinctSatuanKerja()
    {
        return static::whereNotNull('satuan_kerja')
            ->where('satuan_kerja', '!=', '')
            ->distinct()
            ->pluck('satuan_kerja')
            ->sort()
            ->values();
    }

    /**
     * Get distinct status aktif for filter options
     */
    public static function getDistinctStatusAktif()
    {
        return static::whereNotNull('status_aktif')
            ->where('status_aktif', '!=', '')
            ->distinct()
            ->pluck('status_aktif')
            ->sort()
            ->values();
    }

    /**
     * Get distinct jabatan fungsional for filter options
     */
    public static function getDistinctJabatanFungsional()
    {
        return static::whereNotNull('jabatan_fungsional')
            ->where('jabatan_fungsional', '!=', '')
            ->distinct()
            ->pluck('jabatan_fungsional')
            ->sort()
            ->values();
    }

    /**
     * Accessors for compatibility with Employee model
     */
    public function getNamaLengkapAttribute()
    {
        return $this->nama;
    }

    public function getJenisKelaminTextAttribute()
    {
        return match ($this->jenis_kelamin) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-'
        };
    }

    public function getStatusPerkawinanTextAttribute()
    {
        return match ($this->status_nikah) {
            'S' => 'Single',
            'M' => 'Menikah',
            'D' => 'Duda',
            'J' => 'Janda',
            default => $this->status_nikah ?? '-'
        };
    }

}
