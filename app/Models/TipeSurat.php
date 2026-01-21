<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipeSurat extends Model
{
    use SoftDeletes;

    protected $table = 'tipe_surat';

    protected $fillable = [
        'nama_tipe',
        'deskripsi',
        'created_by',
    ];

    /**
     * Get the user who created the tipe surat.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope search by nama tipe.
     */
    public function scopeSearch($query, $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where('nama_tipe', 'like', '%'.$search.'%')
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
     * Get all tipe surat as options for dropdown.
     */
    public static function getDropdownOptions(): array
    {
        return self::active()
            ->orderBy('nama_tipe')
            ->pluck('nama_tipe', 'nama_tipe')
            ->toArray();
    }

    /**
     * Get or create tipe surat by name.
     */
    public static function getOrCreate($namaTipe, $deskripsi = null, $createdBy = null)
    {
        $tipeSurat = self::where('nama_tipe', $namaTipe)->first();

        if (! $tipeSurat) {
            $tipeSurat = self::create([
                'nama_tipe' => $namaTipe,
                'deskripsi' => $deskripsi,
                'created_by' => $createdBy ?? auth()->id(),
            ]);
        }

        return $tipeSurat;
    }

    /**
     * Get count of surat keputusan using this tipe.
     */
    public function getSuratKeputusanCountAttribute(): int
    {
        return \App\Models\SuratKeputusan::where('tipe_surat', $this->nama_tipe)->count();
    }

    /**
     * Check if this tipe surat can be deleted.
     */
    public function getCanBeDeletedAttribute(): bool
    {
        return $this->surat_keputusan_count === 0;
    }
}
