<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'adms_id',
        'pin',
        'name',
        'datetime',
        'status',
        'verify',
        'workcode',
        'mesin_finger_id',
        'user_id',
        'raw_data',
        'processed_at',
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'raw_data' => 'array',
        'status' => 'integer',
        'verify' => 'integer',
        'workcode' => 'integer',
        'processed_at' => 'datetime',
    ];

    // Relasi dengan mesin finger
    public function mesinFinger()
    {
        return $this->belongsTo(MesinFinger::class);
    }

    // Relasi dengan user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope untuk filter berdasarkan tanggal
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('datetime', $date);
    }

    // Scope untuk filter berdasarkan rentang tanggal
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('datetime', [$startDate, $endDate]);
    }

    // Scope untuk filter berdasarkan PIN
    public function scopeByPin($query, $pin)
    {
        return $query->where('pin', $pin);
    }

    // Scope untuk filter berdasarkan user
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope untuk filter yang sudah ter-mapping dengan user
    public function scopeMapped($query)
    {
        return $query->whereNotNull('user_id');
    }

    // Scope untuk filter yang belum ter-mapping dengan user
    public function scopeUnmapped($query)
    {
        return $query->whereNull('user_id');
    }
}
