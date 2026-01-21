<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlipGajiHeader extends Model
{
    protected $table = 'slip_gaji_header';

    protected $fillable = [
        'periode',
        'mode',
        'file_original',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the periode attribute as formatted string for display
     */
    public function getFormattedPeriodeAttribute()
    {
        try {
            $periode = $this->getRawOriginal('periode');
            if (empty($periode)) {
                return 'Periode tidak valid';
            }

            return \Carbon\Carbon::createFromFormat('Y-m', $periode)->format('F Y');
        } catch (\Exception $e) {
            return 'Periode tidak valid'; // Return consistent string if parsing fails
        }
    }

    /**
     * Get raw periode value
     */
    public function getRawPeriode()
    {
        return $this->getRawOriginal('periode');
    }

    /**
     * Relasi ke SlipGajiDetail
     */
    public function details(): HasMany
    {
        return $this->hasMany(SlipGajiDetail::class, 'header_id');
    }

    /**
     * Relasi ke User (yang mengupload)
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
