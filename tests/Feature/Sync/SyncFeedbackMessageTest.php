<?php

namespace Tests\Feature\Sync;

use App\Livewire\Sdm\DosenManagement;
use App\Livewire\Sdm\EmployeeManagement;
use Livewire\Livewire;
use Tests\TestCase;

class SyncFeedbackMessageTest extends TestCase
{
    public function test_employee_management_shows_flash_feedback_message_after_redirect(): void
    {
        session()->flash('success', 'Sinkronisasi berjalan di background.');

        Livewire::test(EmployeeManagement::class)
            ->assertSet('syncMessage', 'Sinkronisasi berjalan di background.');
    }

    public function test_dosen_management_shows_flash_feedback_message_after_redirect(): void
    {
        session()->flash('warning', 'Sinkronisasi selesai dengan warning.');

        Livewire::test(DosenManagement::class)
            ->assertSet('syncMessage', 'Sinkronisasi selesai dengan warning.');
    }
}
