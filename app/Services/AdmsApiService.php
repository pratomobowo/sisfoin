<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdmsApiService
{
    private string $baseUrl;
    private string $token;
    private int $timeout = 30;

    public function __construct()
    {
        $this->baseUrl = config('adms.api_url', env('ADMS_API_URL'));
        $this->token = config('adms.api_token', env('ADMS_API_TOKEN'));

        if (empty($this->baseUrl) || empty($this->token)) {
            Log::warning('AdmsApiService: ADMS API URL or Token not configured');
        }
    }

    /**
     * Test connection to ADMS API
     */
    public function testConnection(): array
    {
        try {
            // Test with minimal date range
            $today = now()->format('Y-m-d');
            $response = $this->makeRequest('/hr/attendances', [
                'start_date' => $today,
                'end_date' => $today,
                'limit' => 1,
            ]);

            return [
                'success' => true,
                'message' => 'Connection to ADMS API successful',
                'api_url' => $this->baseUrl,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'api_url' => $this->baseUrl,
            ];
        }
    }

    /**
     * Get attendance logs from ADMS API
     */
    public function getAttendanceLogs(string $startDate, string $endDate, ?string $employeeId = null, int $limit = 100, int $offset = 0): array
    {
        try {
            $params = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'limit' => $limit,
                'offset' => $offset,
            ];

            if ($employeeId) {
                $params['employee_id'] = $employeeId;
            }

            Log::info('AdmsApiService::getAttendanceLogs - Fetching data', $params);

            $response = $this->makeRequest('/hr/attendances', $params);

            if (!$response['success']) {
                return $response;
            }

            // Transform data to match our attendance_logs format
            $transformedData = $this->transformAttendanceData($response['data']);

            Log::info('AdmsApiService::getAttendanceLogs - Success', [
                'total' => $response['meta']['total'] ?? 0,
                'count' => count($transformedData),
            ]);

            return [
                'success' => true,
                'message' => 'Successfully retrieved ' . count($transformedData) . ' attendance records',
                'data' => $transformedData,
                'meta' => $response['meta'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('AdmsApiService::getAttendanceLogs - Error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get attendance logs: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Get all attendance logs with pagination handling
     */
    public function getAllAttendanceLogs(string $startDate, string $endDate, ?string $employeeId = null): array
    {
        $allData = [];
        $offset = 0;
        $limit = 100;
        $totalFetched = 0;

        try {
            do {
                $result = $this->getAttendanceLogs($startDate, $endDate, $employeeId, $limit, $offset);

                if (!$result['success']) {
                    return $result;
                }

                $allData = array_merge($allData, $result['data']);
                $totalFetched += count($result['data']);
                $offset += $limit;

                $total = $result['meta']['total'] ?? 0;

                Log::info('AdmsApiService::getAllAttendanceLogs - Progress', [
                    'fetched' => $totalFetched,
                    'total' => $total,
                ]);

            } while ($totalFetched < $total && !empty($result['data']));

            return [
                'success' => true,
                'message' => 'Successfully retrieved ' . count($allData) . ' attendance records',
                'data' => $allData,
                'meta' => [
                    'total' => count($allData),
                ],
            ];

        } catch (\Exception $e) {
            Log::error('AdmsApiService::getAllAttendanceLogs - Error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get all attendance logs: ' . $e->getMessage(),
                'data' => $allData,
            ];
        }
    }

    /**
     * Get attendance by ID
     */
    public function getAttendanceById(int $id): array
    {
        try {
            $response = $this->makeRequest("/hr/attendances/{$id}");

            if (!$response['success']) {
                return $response;
            }

            return [
                'success' => true,
                'data' => $this->transformSingleAttendance($response['data']),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get attendance: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Get attendances by employee
     */
    public function getAttendancesByEmployee(string $employeeId, string $startDate, string $endDate): array
    {
        return $this->getAllAttendanceLogs($startDate, $endDate, $employeeId);
    }

    /**
     * Make HTTP request to ADMS API
     */
    private function makeRequest(string $endpoint, array $params = []): array
    {
        $url = rtrim($this->baseUrl, '/') . $endpoint;

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])
            ->get($url, $params);

        if ($response->failed()) {
            $body = $response->json();
            $errorMessage = $body['error']['message'] ?? $response->body();
            
            throw new \Exception("ADMS API Error: {$errorMessage}");
        }

        $data = $response->json();

        if (!isset($data['success']) || !$data['success']) {
            $errorMessage = $data['error']['message'] ?? 'Unknown error';
            throw new \Exception("ADMS API Error: {$errorMessage}");
        }

        return $data;
    }

    /**
     * Transform ADMS attendance data to our format
     */
    private function transformAttendanceData(array $data): array
    {
        return array_map(function ($item) {
            return $this->transformSingleAttendance($item);
        }, $data);
    }

    /**
     * Transform single attendance record
     * 
     * ADMS Format:
     * - employee_id: ID karyawan di mesin
     * - timestamp: waktu absensi
     * - device_sn: serial number device
     * - check_type: false=Check In, true=Check Out
     * - verify_mode: true=Fingerprint, dll
     * - status: {status1, status2, ...}
     */
    private function transformSingleAttendance(array $item): array
    {
        // Parse timestamp
        $datetime = Carbon::parse($item['timestamp']);

        // Map check_type: false=0(Check In), true=1(Check Out)
        $status = $item['check_type'] === false || $item['check_type'] === 0 ? 0 : 1;

        // Map verify_mode: true=1(Fingerprint)
        $verify = $item['verify_mode'] === true || $item['verify_mode'] === 1 ? 1 : 0;

        return [
            'adms_id' => $item['id'],
            'pin' => (string) $item['employee_id'],
            'name' => null, // Will be mapped from user
            'datetime' => $datetime->format('Y-m-d H:i:s'),
            'status' => $status,
            'verify' => $verify,
            'workcode' => $item['work_code'] ?? 0,
            'device_sn' => $item['device_sn'] ?? null,
            'raw_data' => $item,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
