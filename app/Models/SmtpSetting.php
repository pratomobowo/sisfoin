<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'gmail_email',
        'gmail_password',
        'gmail_from_name',
        'is_active'
    ];

    protected $hidden = [
        'gmail_password'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the decrypted Gmail password
     */
    public function getGmailPasswordAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    /**
     * Set the encrypted Gmail password
     */
    public function setGmailPasswordAttribute($value)
    {
        $this->attributes['gmail_password'] = $value ? encrypt($value) : null;
    }

    /**
     * Get the active SMTP configuration
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Scope a query to only include active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive settings
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
