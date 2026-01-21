<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriSk extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_sk';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'created_by',
    ];

    /**
     * Get the user who created the kategori SK.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope search by nama kategori.
     */
    public function scopeSearch($query, $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where('nama_kategori', 'like', '%'.$search.'%')
            ->orWhere('deskripsi', 'like', '%'.$search.'%');
    }

    /**
     * Scope active records only.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get all kategori SK as options for dropdown.
     */
    public static function getDropdownOptions(): array
    {
        return self::active()
            ->orderBy('nama_kategori')
            ->pluck('nama_kategori', 'nama_kategori')
            ->toArray();
    }

    /**
     * Get or create kategori SK by name.
     */
    public static function getOrCreate($namaKategori, $deskripsi = null, $createdBy = null)
    {
        $kategoriSk = self::where('nama_kategori', $namaKategori)->first();

        if (! $kategoriSk) {
            $kategoriSk = self::create([
                'nama_kategori' => $namaKategori,
                'deskripsi' => $deskripsi,
                'created_by' => $createdBy ?? auth()->id(),
            ]);
        }

        return $kategoriSk;
    }

    /**
     * Get count of surat keputusan using this kategori.
     */
    public function getSuratKeputusanCountAttribute(): int
    {
        return \App\Models\SuratKeputusan::where('kategori_sk', $this->nama_kategori)->count();
    }

    /**
     * Check if this kategori SK can be deleted.
     */
    public function getCanBeDeletedAttribute(): bool
    {
        return $this->surat_keputusan_count === 0;
    }
}
