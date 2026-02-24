<?php

namespace Tests\Feature\Staff;

use App\Http\Controllers\Employee\AnnouncementController;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class StaffAnnouncementFlowTest extends TestCase
{
    public function test_staff_announcement_routes_point_to_controller_methods(): void
    {
        $indexRoute = Route::getRoutes()->getByName('staff.pengumuman.index');
        $showRoute = Route::getRoutes()->getByName('staff.pengumuman.show');
        $markAsReadRoute = Route::getRoutes()->getByName('staff.pengumuman.mark-as-read');

        $this->assertNotNull($indexRoute);
        $this->assertNotNull($showRoute);
        $this->assertNotNull($markAsReadRoute);

        $this->assertSame(AnnouncementController::class.'@index', $indexRoute->getActionName());
        $this->assertSame(AnnouncementController::class.'@show', $showRoute->getActionName());
        $this->assertSame(AnnouncementController::class.'@markAsRead', $markAsReadRoute->getActionName());
    }

    public function test_announcement_controller_no_longer_contains_dummy_data_helpers(): void
    {
        $this->assertFalse(method_exists(AnnouncementController::class, 'generateDummyAnnouncementData'));
        $this->assertFalse(method_exists(AnnouncementController::class, 'getDummyAnnouncementById'));
    }

    public function test_announcement_controller_has_real_payload_mapping_helpers(): void
    {
        $this->assertTrue(method_exists(AnnouncementController::class, 'toStaffPayload'));
        $this->assertTrue(method_exists(AnnouncementController::class, 'toStaffPayloadCollection'));
    }

    public function test_mark_as_read_uses_json_response_return_type(): void
    {
        $method = new ReflectionMethod(AnnouncementController::class, 'markAsRead');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertSame('Illuminate\\Http\\JsonResponse', $returnType->getName());
    }
}
