<?php

namespace Tests\Feature\Sync;

use App\Services\SevimaApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SevimaApiServiceFallbackTest extends TestCase
{
    public function test_get_dosen_uses_next_page_url_when_meta_missing(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push([
                    'data' => [
                        ['attributes' => ['id_pegawai' => 'D001', 'nama' => 'A']],
                    ],
                    'links' => ['next' => 'https://api.example.test/dosen?page=2'],
                ], 200)
                ->push([
                    'data' => [
                        ['attributes' => ['id_pegawai' => 'D002', 'nama' => 'B']],
                    ],
                    'links' => ['next' => null],
                ], 200),
        ]);

        config()->set('services.sevima.base_url', 'https://api.example.test');
        config()->set('services.sevima.app_key', 'key');
        config()->set('services.sevima.secret_key', 'secret');

        $service = new SevimaApiService;
        $result = $service->getDosen();

        $this->assertCount(2, $result);
        $this->assertSame('D001', $result[0]['id_pegawai']);
        $this->assertSame('D002', $result[1]['id_pegawai']);
    }
}
