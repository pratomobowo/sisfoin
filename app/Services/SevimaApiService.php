<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SevimaApiService
{
    protected $baseUrl;

    protected $appKey;

    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.sevima.base_url');
        $this->appKey = config('services.sevima.app_key');
        $this->secretKey = config('services.sevima.secret_key');
    }

    /**
     * Get HTTP client with authentication headers
     */
    private function getHttpClient()
    {
        return Http::withHeaders([
            'X-App-Key' => $this->appKey,
            'X-Secret-Key' => $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->timeout((int) config('services.sevima.timeout', 30))
            ->connectTimeout((int) config('services.sevima.connect_timeout', 10))
            ->retry((int) config('services.sevima.retry_times', 2), (int) config('services.sevima.retry_sleep_ms', 200), throw: false)
            ->baseUrl($this->baseUrl);
    }

    /**
     * Log info message
     */
    private function info($message)
    {
        Log::info($message);
        if (app()->runningInConsole()) {
            echo $message."\n";
        }
    }

    /**
     * Fetch data from pegawai endpoint
     */
    public function getPegawai()
    {
        try {
            $response = $this->getHttpClient()
                ->get('/pegawai');

            if ($response->successful()) {
                $data = $response->json();

                // Handle JSON API format with data wrapper
                if (isset($data['data']) && is_array($data['data'])) {
                    // Extract attributes from each record
                    return array_map(function ($record) {
                        return $record['attributes'] ?? $record;
                    }, $data['data']);
                }

                // If data is already an array of records with attributes
                if (is_array($data) && isset($data[0]['attributes'])) {
                    return array_map(function ($record) {
                        return $record['attributes'] ?? $record;
                    }, $data);
                }

                return $data;
            }

            Log::error('Sevima API Error - Pegawai', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new Exception('Failed to fetch pegawai data: '.$response->status());
        } catch (Exception $e) {
            Log::error('Sevima API Exception - Pegawai', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Error fetching pegawai data: '.$e->getMessage());
        }
    }

    /**
     * Fetch data from dosen endpoint with pagination
     */
    public function getDosen()
    {
        try {
            $allDosen = [];
            $currentPage = 1;
            $lastPage = null;
            $nextUrl = null;
            $safetyCounter = 0;

            do {
                $request = $this->getHttpClient();

                if ($nextUrl) {
                    $response = $request->get($nextUrl);
                } else {
                    $response = $request->get('/dosen', ['page' => $currentPage]);
                }

                if ($response->successful()) {
                    $data = $response->json();

                    // Get pagination info
                    if (isset($data['meta'])) {
                        $lastPage = $data['meta']['last_page'] ?? 1;
                        $this->info("Fetching dosen page {$currentPage} of {$lastPage}...");
                    }

                    // Handle JSON API format with data wrapper
                    if (isset($data['data']) && is_array($data['data'])) {
                        // Extract attributes from each record
                        $pageDosen = array_map(function ($record) {
                            return $record['attributes'] ?? $record;
                        }, $data['data']);

                        $allDosen = array_merge($allDosen, $pageDosen);
                    }
                    $nextUrl = $data['links']['next'] ?? null;
                    $safetyCounter++;

                    if ($lastPage !== null) {
                        $currentPage++;
                    }

                    if ($safetyCounter > 1000) {
                        throw new Exception('Dosen pagination safety stop triggered.');
                    }
                } else {
                    Log::error('Sevima API Error - Dosen', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'page' => $currentPage,
                    ]);

                    throw new Exception('Failed to fetch dosen data: '.$response->status());
                }
            } while (($lastPage !== null && $currentPage <= $lastPage) || ($lastPage === null && $nextUrl));

            $this->info('Total dosen records fetched: '.count($allDosen));

            return $allDosen;

        } catch (Exception $e) {
            Log::error('Sevima API Exception - Dosen', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Error fetching dosen data: '.$e->getMessage());
        }
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $response = $this->getHttpClient()
                ->get('/pegawai');

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Sevima API Connection Test Failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Map pegawai API data to employee database format
     */
    public function mapPegawaiToEmployee(array $pegawaiData)
    {
        return [
            'id_pegawai' => $pegawaiData['id_pegawai'] ?? null,
            'nip' => $pegawaiData['nip'] ?? null,
            'nip_pns' => $pegawaiData['nip_pns'] ?? null,
            'nidn' => $pegawaiData['nidn'] ?? null,
            'nup' => $pegawaiData['nup'] ?? null,
            'nidk' => $pegawaiData['nidk'] ?? null,
            'nupn' => $pegawaiData['nupn'] ?? null,
            'nik' => $pegawaiData['nik'] ?? null,
            'nama' => $pegawaiData['nama'] ?? null,
            'gelar_depan' => $pegawaiData['gelar_depan'] ?? null,
            'gelar_belakang' => $pegawaiData['gelar_belakang'] ?? null,
            'jenis_kelamin' => $pegawaiData['jenis_kelamin'] ?? null,
            'id_agama' => $pegawaiData['id_agama'] ?? null,
            'agama' => $pegawaiData['agama'] ?? null,
            'id_kewarganegaraan' => $pegawaiData['id_kewarganegaraan'] ?? null,
            'kewarganegaraan' => $pegawaiData['kewarganegaraan'] ?? null,
            'tanggal_lahir' => $pegawaiData['tanggal_lahir'] ?? null,
            'tempat_lahir' => $pegawaiData['tempat_lahir'] ?? null,
            'status_nikah' => $pegawaiData['status_nikah'] ?? null,
            'alamat_domisili' => $pegawaiData['alamat_domisili'] ?? null,
            'rt_domisili' => $pegawaiData['rt_domisili'] ?? null,
            'rw_domisili' => $pegawaiData['rw_domisili'] ?? null,
            'kode_pos_domisili' => $pegawaiData['kode_pos_domisili'] ?? null,
            'id_kecamatan_domisili' => $pegawaiData['id_kecamatan_domisili'] ?? null,
            'kecamatan_domisili' => $pegawaiData['kecamatan_domisili'] ?? null,
            'id_kota_domisili' => $pegawaiData['id_kota_domisili'] ?? null,
            'kota_domisili' => $pegawaiData['kota_domisili'] ?? null,
            'id_provinsi_domisili' => $pegawaiData['id_provinsi_domisili'] ?? null,
            'provinsi_domisili' => $pegawaiData['provinsi_domisili'] ?? null,
            'alamat_ktp' => $pegawaiData['alamat_ktp'] ?? null,
            'rt_ktp' => $pegawaiData['rt_ktp'] ?? null,
            'rw_ktp' => $pegawaiData['rw_ktp'] ?? null,
            'kode_pos_ktp' => $pegawaiData['kode_pos_ktp'] ?? null,
            'id_kecamatan_ktp' => $pegawaiData['id_kecamatan_ktp'] ?? null,
            'kecamatan_ktp' => $pegawaiData['kecamatan_ktp'] ?? null,
            'id_kota_ktp' => $pegawaiData['id_kota_ktp'] ?? null,
            'kota_ktp' => $pegawaiData['kota_ktp'] ?? null,
            'id_provinsi_ktp' => $pegawaiData['id_provinsi_ktp'] ?? null,
            'provinsi_ktp' => $pegawaiData['provinsi_ktp'] ?? null,
            'nomor_hp' => $pegawaiData['nomor_hp'] ?? null,
            'email' => $pegawaiData['email'] ?? null,
            'email_kampus' => $pegawaiData['email_kampus'] ?? null,
            'id_satuan_kerja' => $pegawaiData['id_satuan_kerja'] ?? null,
            'satuan_kerja' => $pegawaiData['satuan_kerja'] ?? null,
            'id_home_base' => $pegawaiData['id_home_base'] ?? null,
            'home_base' => $pegawaiData['home_base'] ?? null,
            'telepon' => $pegawaiData['telepon'] ?? null,
            'telepon_kantor' => $pegawaiData['telepon_kantor'] ?? null,
            'telepon_alternatif' => $pegawaiData['telepon_alternatif'] ?? null,
            'id_pendidikan_terakhir' => $pegawaiData['id_pendidikan_terakhir'] ?? null,
            'tanggal_masuk' => $pegawaiData['tanggal_masuk'] ?? null,
            'tanggal_sertifikasi_dosen' => $pegawaiData['tanggal_sertifikasi_dosen'] ?? null,
            'id_status_aktif' => $pegawaiData['id_status_aktif'] ?? null,
            'status_aktif' => $pegawaiData['status_aktif'] ?? null,
            'id_status_kepegawaian' => $pegawaiData['id_status_kepegawaian'] ?? null,
            'status_kepegawaian' => $pegawaiData['status_kepegawaian'] ?? null,
            'id_pangkat' => $pegawaiData['id_pangkat'] ?? null,
            'id_jabatan_fungsional' => $pegawaiData['id_jabatan_fungsional'] ?? null,
            'jabatan_fungsional' => $pegawaiData['jabatan_fungsional'] ?? null,
            'id_jabatan_sub_fungsional' => $pegawaiData['id_jabatan_sub_fungsional'] ?? null,
            'jabatan_sub_fungsional' => $pegawaiData['jabatan_sub_fungsional'] ?? null,
            'id_jabatan_struktural' => $pegawaiData['id_jabatan_struktural'] ?? null,
            'jabatan_struktural' => $pegawaiData['jabatan_struktural'] ?? null,
            'is_deleted' => $pegawaiData['is_deleted'] ?? '0',
            'id_sso' => $pegawaiData['id_sso'] ?? null,
            'last_sync_at' => now(),
        ];
    }

    /**
     * Map dosen API data to dosen database format
     */
    public function mapDosenToDosen(array $dosenData)
    {
        return [
            'id_pegawai' => $dosenData['id_pegawai'] ?? null,
            'nip' => $dosenData['nip'] ?? null,
            'nip_pns' => $dosenData['nip_pns'] ?? null,
            'nidn' => $dosenData['nidn'] ?? null,
            'nup' => $dosenData['nup'] ?? null,
            'nidk' => $dosenData['nidk'] ?? null,
            'nupn' => $dosenData['nupn'] ?? null,
            'nik' => $dosenData['nik'] ?? null,
            'nama' => $dosenData['nama'] ?? null,
            'gelar_depan' => $dosenData['gelar_depan'] ?? null,
            'gelar_belakang' => $dosenData['gelar_belakang'] ?? null,
            'jenis_kelamin' => $dosenData['jenis_kelamin'] ?? null,
            'id_agama' => $this->parseInt($dosenData['id_agama'] ?? null),
            'agama' => $dosenData['agama'] ?? null,
            'id_kewarganegaraan' => $dosenData['id_kewarganegaraan'] ?? null,
            'kewarganegaraan' => $dosenData['kewarganegaraan'] ?? null,
            'tanggal_lahir' => $this->formatDate($dosenData['tanggal_lahir'] ?? null),
            'tempat_lahir' => $dosenData['tempat_lahir'] ?? null,
            'status_nikah' => $dosenData['status_nikah'] ?? null,
            'alamat_domisili' => $dosenData['alamat_domisili'] ?? null,
            'rt_domisili' => $dosenData['rt_domisili'] ?? null,
            'rw_domisili' => $dosenData['rw_domisili'] ?? null,
            'kode_pos_domisili' => $dosenData['kode_pos_domisili'] ?? null,
            'id_kecamatan_domisili' => $this->parseInt($dosenData['id_kecamatan_domisili'] ?? null),
            'kecamatan_domisili' => $dosenData['kecamatan_domisili'] ?? null,
            'id_kota_domisili' => $this->parseInt($dosenData['id_kota_domisili'] ?? null),
            'kota_domisili' => $dosenData['kota_domisili'] ?? null,
            'id_provinsi_domisili' => $this->parseInt($dosenData['id_provinsi_domisili'] ?? null),
            'provinsi_domisili' => $dosenData['provinsi_domisili'] ?? null,
            'alamat_ktp' => $dosenData['alamat_ktp'] ?? null,
            'rt_ktp' => $dosenData['rt_ktp'] ?? null,
            'rw_ktp' => $dosenData['rw_ktp'] ?? null,
            'kode_pos_ktp' => $dosenData['kode_pos_ktp'] ?? null,
            'id_kecamatan_ktp' => $this->parseInt($dosenData['id_kecamatan_ktp'] ?? null),
            'kecamatan_ktp' => $dosenData['kecamatan_ktp'] ?? null,
            'id_kota_ktp' => $this->parseInt($dosenData['id_kota_ktp'] ?? null),
            'kota_ktp' => $dosenData['kota_ktp'] ?? null,
            'id_provinsi_ktp' => $this->parseInt($dosenData['id_provinsi_ktp'] ?? null),
            'provinsi_ktp' => $dosenData['provinsi_ktp'] ?? null,
            'nomor_hp' => $dosenData['nomor_hp'] ?? null,
            'email' => $dosenData['email'] ?? null,
            'email_kampus' => $dosenData['email_kampus'] ?? null,
            'telepon' => $dosenData['telepon'] ?? null,
            'telepon_kantor' => $dosenData['telepon_kantor'] ?? null,
            'telepon_alternatif' => $dosenData['telepon_alternatif'] ?? null,
            'id_satuan_kerja' => $this->parseInt($dosenData['id_satuan_kerja'] ?? null),
            'satuan_kerja' => $dosenData['satuan_kerja'] ?? null,
            'id_home_base' => $this->parseInt($dosenData['id_home_base'] ?? null),
            'home_base' => $dosenData['home_base'] ?? null,
            'id_pendidikan_terakhir' => $this->mapPendidikanTerakhir($dosenData['id_pendidikan_terakhir'] ?? null),
            'tanggal_masuk' => $this->formatDate($dosenData['tanggal_masuk'] ?? null),
            'tanggal_sertifikasi_dosen' => $this->formatDate($dosenData['tanggal_sertifikasi_dosen'] ?? null),
            'id_status_aktif' => $dosenData['id_status_aktif'] ?? null,
            'status_aktif' => $this->mapStatusAktif($dosenData['status_aktif'] ?? null),
            'id_status_kepegawaian' => $dosenData['id_status_kepegawaian'] ?? null,
            'status_kepegawaian' => $dosenData['status_kepegawaian'] ?? null,
            'id_pangkat' => $dosenData['id_pangkat'] ?? null,
            'id_jabatan_fungsional' => $dosenData['id_jabatan_fungsional'] ?? null,
            'jabatan_fungsional' => $dosenData['jabatan_fungsional'] ?? null,
            'id_jabatan_sub_fungsional' => $dosenData['id_jabatan_sub_fungsional'] ?? null,
            'jabatan_sub_fungsional' => $dosenData['jabatan_sub_fungsional'] ?? null,
            'id_jabatan_struktural' => $dosenData['id_jabatan_struktural'] ?? null,
            'jabatan_struktural' => $dosenData['jabatan_struktural'] ?? null,
            'is_deleted' => $dosenData['is_deleted'] ?? false,
            'id_sso' => $this->parseInt($dosenData['id_sso'] ?? null),
            'api_created_at' => $this->formatDateTime($dosenData['created_at'] ?? null),
            'api_updated_at' => $this->formatDateTime($dosenData['updated_at'] ?? null),
            'last_synced_at' => now(),
        ];
    }

    /**
     * Format date for database
     */
    private function formatDate($date)
    {
        if (! $date) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (Exception $e) {
            Log::warning('Failed to format date', ['date' => $date, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Format datetime for database
     */
    private function formatDateTime($datetime)
    {
        if (! $datetime) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($datetime);
        } catch (Exception $e) {
            Log::warning('Failed to format datetime', ['datetime' => $datetime, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Parse integer value, return null for empty strings
     */
    private function parseInt($value)
    {
        if ($value === null || $value === '' || $value === '0') {
            return null;
        }

        return (int) $value;
    }

    /**
     * Map pendidikan terakhir to integer ID
     */
    private function mapPendidikanTerakhir($pendidikan)
    {
        if (! $pendidikan) {
            return null;
        }

        // Map string values to integer IDs
        $pendidikanMap = [
            'SMP' => 1,
            'SMA' => 2,
            'D1' => 3,
            'D2' => 4,
            'D3' => 5,
            'S1' => 6,
            'S2' => 7,
            'S3' => 8,
            'Prof' => 9,
            'Spesialis' => 10,
        ];

        // If it's already an integer, return it
        if (is_numeric($pendidikan)) {
            return (int) $pendidikan;
        }

        // Try to map string values
        return $pendidikanMap[strtoupper($pendidikan)] ?? null;
    }

    /**
     * Map status aktif to standard format
     */
    private function mapStatusAktif($status)
    {
        if (! $status) {
            return 'Tidak Aktif';
        }

        $statusMap = [
            'AA' => 'Aktif',
            'Aktif' => 'Aktif',
            'TA' => 'Tidak Aktif',
            'Tidak Aktif' => 'Tidak Aktif',
            'M' => 'Meninggal Dunia',
            'Meninggal Dunia' => 'Meninggal Dunia',
            'PN' => 'Pensiun Normal',
            'Pensiun Normal' => 'Pensiun Normal',
            'MD' => 'Mengundurkan diri',
            'Mengundurkan diri' => 'Mengundurkan diri',
        ];

        return $statusMap[$status] ?? $status;
    }
}
