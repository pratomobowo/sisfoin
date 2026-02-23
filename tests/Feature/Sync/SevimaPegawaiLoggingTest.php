<?php

namespace Tests\Feature\Sync;

use App\Services\SevimaApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SevimaPegawaiLoggingTest extends TestCase
{
    public function test_get_pegawai_logs_fetch_start_and_total_records(): void
    {
        Log::spy();

        Http::fake([
            '*' => Http::response([
                'data' => [
                    ['attributes' => ['id_pegawai' => 'E001', 'nama' => 'A']],
                    ['attributes' => ['id_pegawai' => 'E002', 'nama' => 'B']],
                ],
            ], 200),
        ]);

        config()->set('services.sevima.base_url', 'https://api.example.test');
        config()->set('services.sevima.app_key', 'key');
        config()->set('services.sevima.secret_key', 'secret');

        $service = new SevimaApiService;
        $result = $service->getPegawai();

        $this->assertCount(2, $result);

        Log::shouldHaveReceived('info')
            ->with('Fetching pegawai records...')
            ->once();

        Log::shouldHaveReceived('info')
            ->with('Total pegawai records fetched: 2')
            ->once();
    }
}
