<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class KegiatanPejabat extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_pejabat';
    
    protected $fillable = [
        'nama_kegiatan',
        'jenis_kegiatan',
        'tempat_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'pejabat_terkait',
        'disposisi_kepada',
        'keterangan',
        'file_lampiran',
        'file_name',
        'file_size',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'pejabat_terkait' => 'array',
        'file_size' => 'integer',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors
    public function getFormattedTanggalMulaiAttribute()
    {
        return $this->tanggal_mulai ? $this->tanggal_mulai->format('d M Y') : '-';
    }

    public function getFormattedTanggalSelesaiAttribute()
    {
        return $this->tanggal_selesai ? $this->tanggal_selesai->format('d M Y') : '-';
    }

    public function getFormattedTanggalRangeAttribute()
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            if ($this->tanggal_mulai->eq($this->tanggal_selesai)) {
                return $this->formatted_tanggal_mulai;
            }
            return $this->formatted_tanggal_mulai . ' - ' . $this->formatted_tanggal_selesai;
        }
        return '-';
    }

    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getFileUrlAttribute()
    {
        if (!$this->file_lampiran) {
            return null;
        }
        return Storage::url($this->file_lampiran);
    }

    public function getPejabatTerkaitNamesAttribute()
    {
        if (!$this->pejabat_terkait || !is_array($this->pejabat_terkait)) {
            return '-';
        }

        $pejabatNames = [];
        foreach ($this->pejabat_terkait as $pejabatId) {
            $dosen = \App\Models\Dosen::find($pejabatId);
            if ($dosen) {
                $pejabatNames[] = $dosen->nama_lengkap_with_gelar;
            }
        }

        return !empty($pejabatNames) ? implode(', ', $pejabatNames) : '-';
    }

    // Scopes
    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('nama_kegiatan', 'like', '%' . $search . '%')
              ->orWhere('jenis_kegiatan', 'like', '%' . $search . '%')
              ->orWhere('tempat_kegiatan', 'like', '%' . $search . '%')
              ->orWhere('keterangan', 'like', '%' . $search . '%')
              ->orWhere('disposisi_kepada', 'like', '%' . $search . '%');
        });
    }

    public function scopeFilterByJenis($query, $jenis)
    {
        if (!$jenis) {
            return $query;
        }

        return $query->where('jenis_kegiatan', $jenis);
    }

    

    public function scopeFilterByPejabat($query, $pejabatId)
    {
        if (!$pejabatId) {
            return $query;
        }

        return $query->whereJsonContains('pejabat_terkait', $pejabatId);
    }

    // Helper methods
    public function hasFile()
    {
        return !empty($this->file_lampiran) && Storage::disk('public')->exists($this->file_lampiran);
    }

    public function deleteFile()
    {
        if ($this->file_lampiran && Storage::disk('public')->exists($this->file_lampiran)) {
            Storage::disk('public')->delete($this->file_lampiran);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($kegiatan) {
            $kegiatan->deleteFile();
        });
    }
}
