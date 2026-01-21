<?php

namespace Database\Factories;

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceLogFactory extends Factory
{
    protected $model = AttendanceLog::class;

    public function definition()
    {
        return [
            'pin' => $this->faker->numerify('###'),
            'name' => $this->faker->name(),
            'datetime' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->numberBetween(0, 255),
            'verify' => $this->faker->numberBetween(0, 255),
            'workcode' => $this->faker->numberBetween(0, 255),
            'mesin_finger_id' => null,
            'user_id' => null,
            'raw_data' => null,
            'processed_at' => null,
        ];
    }

    /**
     * Indicate that the log is mapped to a user.
     */
    public function mapped($userId = null)
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'user_id' => $userId ?? User::factory()->create()->id,
            ];
        });
    }

    /**
     * Indicate that the log is processed.
     */
    public function processed()
    {
        return $this->state(function (array $attributes) {
            return [
                'processed_at' => now(),
            ];
        });
    }

    /**
     * Set a specific datetime for the log.
     */
    public function atDateTime($datetime)
    {
        return $this->state(function (array $attributes) use ($datetime) {
            return [
                'datetime' => $datetime,
            ];
        });
    }

    /**
     * Set a specific pin for the log.
     */
    public function withPin($pin)
    {
        return $this->state(function (array $attributes) use ($pin) {
            return [
                'pin' => $pin,
            ];
        });
    }
}
