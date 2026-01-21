<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MesinFinger extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_mesin',
        'ip_address',
        'port',
        'lokasi',
        'status',
        'keterangan',
        'serial_number',
        'device_model',
        'last_connected_at',
        'device_info',
        'auto_sync',
        'sync_interval',
        'last_error_message',
    ];

    protected $casts = [
        'device_info' => 'array',
        'last_connected_at' => 'datetime',
        'auto_sync' => 'boolean',
        'sync_interval' => 'integer',
        'port' => 'integer',
    ];

    protected $attributes = [
        'status' => 'inactive',
        'port' => 4370,
        'device_model' => 'X100C',
        'auto_sync' => false,
        'sync_interval' => 60,
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';

    const STATUS_ERROR = 'error';

    const STATUS_MAINTENANCE = 'maintenance';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Tidak Aktif',
            self::STATUS_ERROR => 'Error',
            self::STATUS_MAINTENANCE => 'Maintenance',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('lokasi', 'like', "%{$location}%");
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? 'Unknown';
    }

    public function getConnectionStringAttribute()
    {
        return "{$this->ip_address}:{$this->port}";
    }

    public function getIsOnlineAttribute()
    {
        return $this->status === self::STATUS_ACTIVE &&
               $this->last_connected_at &&
               $this->last_connected_at->diffInMinutes(now()) <= 5;
    }

    public function getLastConnectedHumanAttribute()
    {
        return $this->last_connected_at ? $this->last_connected_at->diffForHumans() : 'Belum pernah terhubung';
    }

    // Methods
    public function updateConnectionStatus($isConnected = false, $deviceInfo = null)
    {
        $this->update([
            'status' => $isConnected ? self::STATUS_ACTIVE : self::STATUS_ERROR,
            'last_connected_at' => $isConnected ? now() : $this->last_connected_at,
            'device_info' => $deviceInfo ?: $this->device_info,
        ]);
    }

    public function markAsMaintenanceMode()
    {
        $this->update(['status' => self::STATUS_MAINTENANCE]);
    }

    public function canConnect()
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_ERROR]);
    }

    public function getDeviceSpecifications()
    {
        return $this->device_info ?: [
            'model' => $this->device_model,
            'capacity' => 'Unknown',
            'firmware' => 'Unknown',
            'algorithm' => 'Unknown',
        ];
    }

    /**
     * Update error status
     */
    public function updateErrorStatus($errorMessage)
    {
        $this->update([
            'status' => self::STATUS_ERROR,
            'last_error_message' => $errorMessage,
        ]);
    }

    /**
     * Set machine as maintenance
     */
    public function setMaintenance()
    {
        $this->update(['status' => self::STATUS_MAINTENANCE]);
    }

    /**
     * Activate machine
     */
    public function activate()
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Deactivate machine
     */
    public function deactivate()
    {
        $this->update(['status' => self::STATUS_INACTIVE]);
    }

    /**
     * Scope for online machines
     */
    public function scopeOnline($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('last_connected_at')
            ->where('last_connected_at', '>=', Carbon::now()->subMinutes(5));
    }

    // Relasi dengan attendance logs
    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
