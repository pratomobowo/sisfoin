<?php

namespace Database\Factories\Employee;

use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = EmployeeAttendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'check_in_time' => $this->faker->dateTimeBetween('08:00', '09:00'),
            'check_out_time' => $this->faker->dateTimeBetween('17:00', '18:00'),
            'break_start_time' => null,
            'break_end_time' => null,
            'total_hours' => 8.0,
            'overtime_hours' => 0.0,
            'status' => 'present',
            'notes' => null,
            'location_check_in' => null,
            'location_check_out' => null,
            'ip_address' => null,
            'device_info' => null,
            'created_by' => 1,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the attendance is for a specific user.
     */
    public function forUser($userId)
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'user_id' => $userId,
            ];
        });
    }

    /**
     * Indicate that the attendance is for a specific date.
     */
    public function onDate($date)
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'date' => $date,
            ];
        });
    }

    /**
     * Indicate that the attendance is late.
     */
    public function late()
    {
        return $this->state(function (array $attributes) {
            return [
                'check_in_time' => $this->faker->dateTimeBetween('08:30', '09:30'),
                'status' => 'late',
                'notes' => 'Terlambat 30 menit',
            ];
        });
    }

    /**
     * Indicate that the attendance is half day.
     */
    public function halfDay()
    {
        return $this->state(function (array $attributes) {
            return [
                'check_out_time' => $this->faker->dateTimeBetween('12:00', '13:00'),
                'total_hours' => 4.0,
                'status' => 'half_day',
            ];
        });
    }

    /**
     * Indicate that the attendance is absent.
     */
    public function absent()
    {
        return $this->state(function (array $attributes) {
            return [
                'check_in_time' => null,
                'check_out_time' => null,
                'total_hours' => 0.0,
                'status' => 'absent',
            ];
        });
    }

    /**
     * Indicate that the attendance has overtime.
     */
    public function withOvertime($hours = 2)
    {
        return $this->state(function (array $attributes) use ($hours) {
            return [
                'check_out_time' => $this->faker->dateTimeBetween('19:00', '20:00'),
                'total_hours' => 10.0,
                'overtime_hours' => $hours,
            ];
        });
    }

    /**
     * Indicate that the attendance is created by a specific user.
     */
    public function createdBy($userId)
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'created_by' => $userId,
            ];
        });
    }
}
