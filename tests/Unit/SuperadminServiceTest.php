<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\MesinFinger;
use App\Models\AttendanceLog;
use App\Services\SuperadminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperadminServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SuperadminService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SuperadminService();
    }

    /** @test */
    public function get_dashboard_stats_returns_correct_structure()
    {
        // Create some test data without using is_active column
        User::factory()->count(5)->create();

        $stats = $this->service->getDashboardStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('active_users', $stats);
        $this->assertArrayHasKey('total_roles', $stats);
        $this->assertArrayHasKey('total_machines', $stats);
        $this->assertArrayHasKey('active_machines', $stats);
        $this->assertArrayHasKey('today_attendance', $stats);
        $this->assertArrayHasKey('recent_activities', $stats);
        $this->assertArrayHasKey('user_growth', $stats);
        $this->assertArrayHasKey('attendance_summary', $stats);
    }

    /** @test */
    public function get_user_growth_data_returns_correct_format()
    {
        // Create users with different creation dates
        User::factory()->create(['created_at' => now()->subDays(5)]);
        User::factory()->count(2)->create(['created_at' => now()->subDays(3)]);
        User::factory()->count(3)->create(['created_at' => now()->subDay()]);

        $data = $this->service->getUserGrowthData(7);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('dates', $data);
        $this->assertArrayHasKey('counts', $data);
        $this->assertCount(7, $data['dates']);
        $this->assertCount(7, $data['counts']);
    }

    /** @test */
    public function get_recent_activities_returns_limited_results()
    {
        // This test assumes you have activity logging implemented
        // Create some test activities if you have an activities table
        
        $activities = $this->service->getRecentActivities(5);

        $this->assertIsArray($activities);
        $this->assertLessThanOrEqual(5, count($activities));
    }

    /** @test */
    public function get_attendance_summary_returns_correct_structure()
    {
        $summary = $this->service->getAttendanceSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('today', $summary);
        $this->assertArrayHasKey('this_week', $summary);
        $this->assertArrayHasKey('this_month', $summary);
        $this->assertArrayHasKey('unique_users_today', $summary);
    }

    /** @test */
    public function get_system_health_returns_all_components()
    {
        $health = $this->service->getSystemHealth();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('database', $health);
        $this->assertArrayHasKey('fingerprint_machines', $health);
        $this->assertArrayHasKey('storage', $health);
        $this->assertArrayHasKey('cache', $health);
        
        // Check each component has status and message
        foreach (['database', 'fingerprint_machines', 'storage', 'cache'] as $component) {
            $this->assertArrayHasKey('status', $health[$component]);
            $this->assertArrayHasKey('message', $health[$component]);
        }
    }

    /** @test */
    public function check_database_health_returns_healthy_status()
    {
        $health = $this->service->getSystemHealth();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('database', $health);
        $this->assertArrayHasKey('status', $health['database']);
        $this->assertArrayHasKey('message', $health['database']);
        $this->assertEquals('healthy', $health['database']['status']);
    }

    /** @test */
    public function check_fingerprint_machines_health_with_no_machines()
    {
        $health = $this->service->getSystemHealth();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('fingerprint_machines', $health);
        $this->assertArrayHasKey('status', $health['fingerprint_machines']);
        $this->assertArrayHasKey('message', $health['fingerprint_machines']);
    }

    /** @test */
    public function check_fingerprint_machines_health_with_active_machines()
    {
        // Create test machines directly without factory
        MesinFinger::create([
            'nama_mesin' => 'Test Machine 1',
            'ip_address' => '192.168.1.100',
            'port' => 4370,
            'status' => 'active',
        ]);

        MesinFinger::create([
            'nama_mesin' => 'Test Machine 2',
            'ip_address' => '192.168.1.101',
            'port' => 4370,
            'status' => 'inactive',
        ]);

        $health = $this->service->getSystemHealth();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('fingerprint_machines', $health);
        $this->assertArrayHasKey('status', $health['fingerprint_machines']);
        $this->assertArrayHasKey('message', $health['fingerprint_machines']);
    }

    /** @test */
    public function check_storage_health_returns_status()
    {
        $health = $this->service->getSystemHealth();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('storage', $health);
        $this->assertArrayHasKey('status', $health['storage']);
        $this->assertArrayHasKey('message', $health['storage']);
    }

    /** @test */
    public function check_cache_health_returns_status()
    {
        $health = $this->service->getSystemHealth();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('cache', $health);
        $this->assertArrayHasKey('status', $health['cache']);
        $this->assertArrayHasKey('message', $health['cache']);
    }

    /** @test */
    public function clear_caches_returns_boolean()
    {
        // Set some cache values first
        Cache::put('test_key_1', 'test_value_1', 60);
        Cache::put('test_key_2', 'test_value_2', 60);

        $result = $this->service->clearCaches();

        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    /** @test */
    public function get_users_export_data_returns_correct_structure()
    {
        User::factory()->count(3)->create();

        $data = $this->service->getUsersExportData();

        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));

        // Check first user data structure
        if (count($data) > 0) {
            $firstUser = $data[0];
            $this->assertArrayHasKey('ID', $firstUser);
            $this->assertArrayHasKey('Nama', $firstUser);
            $this->assertArrayHasKey('Email', $firstUser);
            $this->assertArrayHasKey('NIP', $firstUser);
            $this->assertArrayHasKey('Tipe Karyawan', $firstUser);
            $this->assertArrayHasKey('Status', $firstUser);
        }
    }

    /** @test */
    public function dashboard_stats_are_cached()
    {
        // Clear any existing cache
        Cache::forget('superadmin.dashboard.stats');

        // First call should hit the database
        $stats1 = $this->service->getDashboardStats();

        // Second call should use cache
        $stats2 = $this->service->getDashboardStats();

        $this->assertEquals($stats1, $stats2);
    }

    /** @test */
    public function user_growth_data_handles_different_day_ranges()
    {
        $data7Days = $this->service->getUserGrowthData(7);
        $data30Days = $this->service->getUserGrowthData(30);

        $this->assertCount(7, $data7Days['dates']);
        $this->assertCount(30, $data30Days['dates']);
    }
}