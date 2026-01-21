<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerprintUserMapping extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fingerprint_user_mappings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pin',
        'name',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the mesin fingers associated with this user mapping.
     */
    public function mesinFingers()
    {
        return $this->belongsToMany(MesinFinger::class, 'mesin_finger_user_mapping')
                    ->withPivot('synced_at', 'status')
                    ->withTimestamps();
    }

    /**
     * Get the attendance logs for this user.
     */
    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'user_id', 'pin');
    }

    /**
     * Find a user by PIN.
     *
     * @param  string  $pin
     * @return \App\Models\FingerprintUserMapping|null
     */
    public static function findByPin($pin)
    {
        return static::where('pin', $pin)->first();
    }

    /**
     * Create or update a user from machine data.
     *
     * @param  array  $userData
     * @return \App\Models\FingerprintUserMapping
     */
    public static function createOrUpdateFromMachineData(array $userData)
    {
        $pin = $userData['pin'] ?? null;
        $name = $userData['name'] ?? 'Unknown';
        
        if (!$pin) {
            throw new \InvalidArgumentException('PIN is required');
        }

        return static::updateOrCreate(
            ['pin' => $pin],
            [
                'name' => $name,
                'is_active' => true,
            ]
        );
    }

    /**
     * Sync multiple users from machine data.
     *
     * @param  array  $usersData
     * @param  int  $machineId
     * @return array
     */
    public static function syncFromMachineData(array $usersData, $machineId)
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($usersData as $userData) {
            try {
                $user = static::createOrUpdateFromMachineData($userData);
                
                // Check if user was created or updated
                if ($user->wasRecentlyCreated) {
                    $results['created']++;
                } else {
                    $results['updated']++;
                }

                // Sync with mesin finger
                // Note: You might need to create a pivot table for this relationship
                // $user->mesinFingers()->syncWithoutDetaching([$machineId]);

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'pin' => $userData['pin'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
