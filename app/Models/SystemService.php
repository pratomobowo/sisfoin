<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemService extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'is_active',
        'status',
        'last_run_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_run_at' => 'datetime'
    ];
}
