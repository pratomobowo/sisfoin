<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlipGajiDetail extends Model
{
    use HasFactory;

    protected $table = 'slip_gaji_detail';

    protected $fillable = [
        'header_id',
        'status',
        'nip',
        // PENDAPATAN
        'gaji_pokok',
        'tpp',
        'tunjangan_keluarga',
        'tunjangan_kemahalan',
        'tunjangan_kesehatan',
        'insentif_golongan',
        'tunjangan_golongan',
        'tunjangan_pmb',
        'tunjangan_masa_kerja',
        'transport',
        'tunjangan_rumah',
        'tunjangan_pendidikan',
        'tunjangan_struktural',
        'tunjangan_fungsional',
        'beban_manajemen',
        'honor_tetap',
        'insentif_golongan',
        'honor_tunai',
        'penerimaan_kotor',

        // POTONGAN
        'potongan_arisan',
        'potongan_koperasi',
        'potongan_lazmaal',
        'potongan_bpjs_kesehatan',
        'potongan_bpjs_ketenagakerjaan',
        'potongan_bkd',

        // PAJAK
        'pph21_terhutang',
        'pph21_sudah_dipotong',
        'pph21_kurang_dipotong',
        'pajak',

        // TOTAL
        'penerimaan_bersih',
    ];

    protected $casts = [
        // PENDAPATAN
        'gaji_pokok' => 'decimal:2',
        'tpp' => 'decimal:2',
        'tunjangan_keluarga' => 'decimal:2',
        'tunjangan_kemahalan' => 'decimal:2',
        'tunjangan_kesehatan' => 'decimal:2',
        'insentif_golongan' => 'decimal:2',
        'tunjangan_golongan' => 'decimal:2',
        'tunjangan_pmb' => 'decimal:2',
        'tunjangan_masa_kerja' => 'decimal:2',
        'transport' => 'decimal:2',
        'tunjangan_rumah' => 'decimal:2',
        'tunjangan_pendidikan' => 'decimal:2',
        'tunjangan_struktural' => 'decimal:2',
        'tunjangan_fungsional' => 'decimal:2',
        'beban_manajemen' => 'decimal:2',
        'honor_tetap' => 'decimal:2',
        'insentif_golongan' => 'decimal:2',
        'honor_tunai' => 'decimal:2',
        'penerimaan_kotor' => 'decimal:2',

        // POTONGAN
        'potongan_arisan' => 'decimal:2',
        'potongan_koperasi' => 'decimal:2',
        'potongan_lazmaal' => 'decimal:2',
        'potongan_bpjs_kesehatan' => 'decimal:2',
        'potongan_bpjs_ketenagakerjaan' => 'decimal:2',
        'potongan_bkd' => 'decimal:2',

        // PAJAK
        'pph21_terhutang' => 'decimal:2',
        'pph21_sudah_dipotong' => 'decimal:2',
        'pph21_kurang_dipotong' => 'decimal:2',
        'pajak' => 'decimal:2',

        // TOTAL
        'penerimaan_bersih' => 'decimal:2',
    ];

    /**
     * Relasi ke SlipGajiHeader
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(SlipGajiHeader::class, 'header_id');
    }

    /**
     * Relasi ke Employee berdasarkan NIP
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'nip', 'nip');
    }

    /**
     * Relasi ke Dosen berdasarkan NIP
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'nip', 'nip');
    }

    /**
     * Relasi ke EmailLog
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Accessor untuk mendapatkan nama dari Employee atau Dosen
     */
    public function getNamaFromRelationAttribute()
    {
        if ($this->employee) {
            return $this->employee->nama_lengkap_with_gelar;
        }

        if ($this->dosen) {
            return $this->dosen->nama_lengkap_with_gelar;
        }

        // No fallback to nama column since it's being removed
        return 'Nama tidak ditemukan';
    }

    /**
     * Accessor untuk mendapatkan total potongan
     */
    public function getTotalPotonganAttribute()
    {
        return ($this->potongan_arisan ?? 0) +
               ($this->potongan_koperasi ?? 0) +
               ($this->potongan_lazmaal ?? 0) +
               ($this->potongan_bpjs_kesehatan ?? 0) +
               ($this->potongan_bpjs_ketenagakerjaan ?? 0) +
               ($this->potongan_bkd ?? 0) +
               ($this->pph21_kurang_dipotong ?? 0) ;
    }

    /**
     * Accessor untuk mendapatkan total potongan tanpa pajak
     */
    public function getTotalPotonganTanpaPajakAttribute()
    {
        return ($this->potongan_arisan ?? 0) +
               ($this->potongan_koperasi ?? 0) +
               ($this->potongan_lazmaal ?? 0) +
               ($this->potongan_bpjs_kesehatan ?? 0) +
               ($this->potongan_bpjs_ketenagakerjaan ?? 0) +
               ($this->potongan_bkd ?? 0);
    }

    /**
     * Accessor untuk mendapatkan jumlah potongan untuk dosen dpk (total potongan tanpa pajak + pajak)
     */
    public function getTotalPotonganDpkAttribute()
    {
        return $this->total_potongan_tanpa_pajak + ($this->pajak ?? 0);
    }

    /**
     * Accessor untuk mendapatkan data lengkap pegawai
     */
    public function getPegawaiDataAttribute()
    {
        if ($this->employee) {
            return [
                'type' => 'employee',
                'data' => $this->employee,
                'nama_lengkap' => $this->employee->nama_lengkap_with_gelar,
                'satuan_kerja' => $this->employee->satuan_kerja,
                'jabatan' => $this->employee->jabatan_struktural ?: $this->employee->jabatan_fungsional,
                'id_pangkat' => $this->employee->id_pangkat,
                'status_kepegawaian' => $this->employee->status_kepegawaian,
            ];
        }

        if ($this->dosen) {
            return [
                'type' => 'dosen',
                'data' => $this->dosen,
                'nama_lengkap' => $this->dosen->nama_lengkap_with_gelar,
                'satuan_kerja' => $this->dosen->satuan_kerja,
                'jabatan' => $this->dosen->jabatan_struktural ?: $this->dosen->jabatan_fungsional,
                'id_pangkat' => $this->dosen->id_pangkat,
                'status_kepegawaian' => null, // Dosen menggunakan status dari slip_gaji_detail
            ];
        }

        return [
            'type' => 'unknown',
            'data' => null,
            'nama_lengkap' => 'Nama tidak ditemukan',
            'unit_kerja' => null,
            'jabatan' => null,
            'pangkat' => null,
            'status_kepegawaian' => null,
        ];
    }
}
