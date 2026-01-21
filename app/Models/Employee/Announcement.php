<?php

namespace App\Models\Employee;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'employee_announcements';

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'status',
        'published_at',
        'expires_at',
        'target_audience',
        'attachments',
        'is_pinned',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'attachments' => 'array',
        'is_pinned' => 'boolean',
    ];

    /**
     * Get the user who created the announcement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the announcement.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get users who have read this announcement.
     */
    public function readByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_reads')
            ->withTimestamps()
            ->withPivot('read_at');
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'secondary',
            'normal' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Rendah',
            'normal' => 'Normal',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak',
            default => 'Normal'
        };
    }

    /**
     * Get type badge color.
     */
    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'general' => 'info',
            'policy' => 'warning',
            'event' => 'success',
            'urgent' => 'danger',
            'maintenance' => 'secondary',
            default => 'info'
        };
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'general' => 'Umum',
            'policy' => 'Kebijakan',
            'event' => 'Acara',
            'urgent' => 'Mendesak',
            'maintenance' => 'Pemeliharaan',
            default => 'Umum'
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'published' => 'success',
            'archived' => 'warning',
            'expired' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'published' => 'Dipublikasi',
            'archived' => 'Diarsipkan',
            'expired' => 'Kedaluwarsa',
            default => 'Draft'
        };
    }

    /**
     * Get formatted published date.
     */
    public function getFormattedPublishedDateAttribute(): string
    {
        return $this->published_at ? $this->published_at->format('d/m/Y H:i') : '-';
    }

    /**
     * Get formatted expiry date.
     */
    public function getFormattedExpiryDateAttribute(): string
    {
        return $this->expires_at ? $this->expires_at->format('d/m/Y H:i') : 'Tidak ada';
    }

    /**
     * Get excerpt of content.
     */
    public function getExcerptAttribute(): string
    {
        return str_limit(strip_tags($this->content), 150);
    }

    /**
     * Check if announcement is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if announcement is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'published' &&
               ($this->published_at === null || $this->published_at->isPast()) &&
               ! $this->isExpired();
    }

    /**
     * Check if user has read this announcement.
     */
    public function isReadBy(User $user): bool
    {
        return $this->readByUsers()->where('user_id', $user->id)->exists();
    }

    /**
     * Mark as read by user.
     */
    public function markAsReadBy(User $user): void
    {
        if (! $this->isReadBy($user)) {
            $this->readByUsers()->attach($user->id, ['read_at' => now()]);
        }
    }

    /**
     * Scope for published announcements.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for active announcements.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for pinned announcements.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope for filtering by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for unread by user.
     */
    public function scopeUnreadBy($query, User $user)
    {
        return $query->whereDoesntHave('readByUsers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }
}
