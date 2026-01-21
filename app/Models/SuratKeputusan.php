<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratKeputusan extends Model
{
    use SoftDeletes;

    protected $table = 'surat_keputusan';

    protected $fillable = [
        'nomor_surat',
        'tipe_surat',
        'kategori_sk',
        'tentang',
        'tanggal_penetapan',
        'tanggal_berlaku',
        'ditandatangani_oleh',
        'deskripsi',
        'file_path',
        'file_name',
        'file_size',
        'created_by',
    ];

    protected $casts = [
        'tanggal_penetapan' => 'date',
        'tanggal_berlaku' => 'date',
        'file_size' => 'integer',
    ];

    /**
     * Get the user who created the surat keputusan.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the formatted tanggal penetapan.
     */
    public function getFormattedTanggalPenetapanAttribute(): string
    {
        return $this->tanggal_penetapan ? $this->tanggal_penetapan->format('d M Y') : '-';
    }

    /**
     * Get the formatted tanggal berlaku.
     */
    public function getFormattedTanggalBerlakuAttribute(): string
    {
        return $this->tanggal_berlaku ? $this->tanggal_berlaku->format('d M Y') : '-';
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if ($this->file_size < 1024) {
            return $this->file_size.' B';
        } elseif ($this->file_size < 1048576) {
            return round($this->file_size / 1024, 2).' KB';
        } else {
            return round($this->file_size / 1048576, 2).' MB';
        }
    }

    /**
     * Get the file URL.
     */
    public function getFileUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
    }

    /**
     * Check if the surat is still valid.
     */
    public function getIsValidAttribute(): bool
    {
        return $this->tanggal_berlaku && $this->tanggal_berlaku->isFuture();
    }

    /**
     * Get the status text.
     */
    public function getStatusTextAttribute(): string
    {
        if ($this->tanggal_berlaku) {
            return $this->tanggal_berlaku->isFuture() ? 'Berlaku' : 'Kadaluarsa';
        }

        return 'Tanpa Tanggal Berlaku';
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->tanggal_berlaku) {
            return $this->tanggal_berlaku->isFuture() ? 'green' : 'red';
        }

        return 'gray';
    }

    /**
     * Scope search by multiple fields.
     */
    public function scopeSearch($query, $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('nomor_surat', 'like', '%'.$search.'%')
                ->orWhere('tipe_surat', 'like', '%'.$search.'%')
                ->orWhere('kategori_sk', 'like', '%'.$search.'%')
                ->orWhere('tentang', 'like', '%'.$search.'%')
                ->orWhere('ditandatangani_oleh', 'like', '%'.$search.'%')
                ->orWhere('deskripsi', 'like', '%'.$search.'%');
        });
    }

    /**
     * Scope filter by tipe surat.
     */
    public function scopeFilterByTipeSurat($query, $tipeSurat)
    {
        if (! $tipeSurat) {
            return $query;
        }

        return $query->where('tipe_surat', $tipeSurat);
    }

    /**
     * Scope filter by kategori SK.
     */
    public function scopeFilterByKategoriSk($query, $kategoriSk)
    {
        if (! $kategoriSk) {
            return $query;
        }

        return $query->where('kategori_sk', $kategoriSk);
    }

    /**
     * Scope filter by date range.
     */
    public function scopeFilterByDateRange($query, $startDate, $endDate)
    {
        if (! $startDate && ! $endDate) {
            return $query;
        }

        if ($startDate && $endDate) {
            return $query->whereBetween('tanggal_penetapan', [$startDate, $endDate]);
        }

        if ($startDate) {
            return $query->where('tanggal_penetapan', '>=', $startDate);
        }

        if ($endDate) {
            return $query->where('tanggal_penetapan', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope filter by pejabat.
     */
    public function scopeFilterByPejabat($query, $pejabat)
    {
        if (! $pejabat) {
            return $query;
        }

        return $query->where('ditandatangani_oleh', $pejabat);
    }
}
