<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'label',
        'description',
        'group',
        'sort_order',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("attendance_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return match ($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'array' => explode(',', $setting->value),
                'time' => $setting->value,
                default => $setting->value,
            };
        });
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value): void
    {
        $setting = static::where('key', $key)->first();
        
        if ($setting) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            
            $setting->update(['value' => $value]);
            Cache::forget("attendance_setting_{$key}");
        }
    }

    /**
     * Get all settings grouped
     */
    public static function getAllGrouped()
    {
        return static::orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("attendance_setting_{$key}");
        }
    }
}
