<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'guard_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the display name of the role.
     *
     * @return string
     */
    public function getDisplayNameAttribute($value)
    {
        return $value ?? ucfirst(str_replace('-', ' ', $this->name));
    }

    /**
     * Scope a query to only include roles with a specific name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithName($query, $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Get all available roles as options for dropdown/select.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getRoleOptions()
    {
        return static::all()->mapWithKeys(function ($role) {
            return [$role->id => $role->display_name];
        });
    }
}
