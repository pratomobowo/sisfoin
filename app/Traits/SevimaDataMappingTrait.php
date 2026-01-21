<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

trait SevimaDataMappingTrait
{
    /**
     * Validate and sanitize pegawai data
     */
    protected function validateAndSanitizePegawaiData(array $pegawaiData)
    {
        try {
            // Basic validation rules
            $rules = [
                'nik' => 'nullable|string|max:20',
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'nullable|in:L,P',
                'tanggal_lahir' => 'nullable|date',
                'tempat_lahir' => 'nullable|string|max:100',
                'agama' => 'nullable|string|max:50',
                'status_nikah' => 'nullable|in:S,M,D,J',
                'kewarganegaraan' => 'nullable|string|max:50',
                'alamat_ktp' => 'nullable|string|max:255',
                'alamat_domisili' => 'nullable|string|max:255',
                'telepon' => 'nullable|string|max:20',
                'nomor_hp' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'email_kampus' => 'nullable|email|max:255',
                'nip' => 'nullable|string|max:50',
                'status_kepegawaian' => 'nullable|string|max:50',
                'tanggal_masuk' => 'nullable|date',
                'tanggal_keluar' => 'nullable|date|after_or_equal:tanggal_masuk',
                'status_aktif' => 'nullable|string|max:50',
                'satuan_kerja' => 'nullable|string|max:100',
                'jabatan_fungsional' => 'nullable|string|max:100',
                'jabatan_struktural' => 'nullable|string|max:100',
            ];

            $validator = Validator::make($pegawaiData, $rules);

            if ($validator->fails()) {
                Log::warning('Pegawai data validation failed', [
                    'data' => $pegawaiData,
                    'errors' => $validator->errors()->toArray(),
                ]);
            }

            // Sanitize data
            $sanitized = [];
            foreach ($pegawaiData as $key => $value) {
                if (is_string($value)) {
                    $sanitized[$key] = trim($value);
                    if (empty($sanitized[$key])) {
                        $sanitized[$key] = null;
                    }
                } else {
                    $sanitized[$key] = $value;
                }
            }

            return $sanitized;
        } catch (Exception $e) {
            Log::error('Error validating pegawai data', [
                'error' => $e->getMessage(),
                'data' => $pegawaiData,
            ]);
            throw $e;
        }
    }

    /**
     * Validate and sanitize dosen data
     */
    protected function validateAndSanitizeDosenData(array $dosenData)
    {
        try {
            // Basic validation rules
            $rules = [
                'nip' => 'nullable|string|max:50',
                'nidn' => 'nullable|string|max:50',
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'nullable|in:L,P',
                'tanggal_lahir' => 'nullable|date',
                'tempat_lahir' => 'nullable|string|max:100',
                'agama' => 'nullable|string|max:50',
                'status_nikah' => 'nullable|in:S,M,D,J',
                'kewarganegaraan' => 'nullable|string|max:50',
                'alamat_domisili' => 'nullable|string|max:255',
                'alamat_ktp' => 'nullable|string|max:255',
                'nomor_hp' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'email_kampus' => 'nullable|email|max:255',
                'satuan_kerja' => 'nullable|string|max:100',
                'jabatan_fungsional' => 'nullable|string|max:100',
                'jabatan_struktural' => 'nullable|string|max:100',
                'status_aktif' => 'nullable|string|max:50',
                'tanggal_masuk' => 'nullable|date',
            ];

            $validator = Validator::make($dosenData, $rules);

            if ($validator->fails()) {
                Log::warning('Dosen data validation failed', [
                    'data' => $dosenData,
                    'errors' => $validator->errors()->toArray(),
                ]);
            }

            // Sanitize data
            $sanitized = [];
            foreach ($dosenData as $key => $value) {
                if (is_string($value)) {
                    $sanitized[$key] = trim($value);
                    if (empty($sanitized[$key])) {
                        $sanitized[$key] = null;
                    }
                } else {
                    $sanitized[$key] = $value;
                }
            }

            return $sanitized;
        } catch (Exception $e) {
            Log::error('Error validating dosen data', [
                'error' => $e->getMessage(),
                'data' => $dosenData,
            ]);
            throw $e;
        }
    }

    /**
     * Process pegawai data batch
     */
    protected function processPegawaiBatch(array $pegawaiList)
    {
        $processed = [];
        $errors = [];

        foreach ($pegawaiList as $index => $pegawai) {
            try {
                if (!is_array($pegawai)) {
                    $errors[] = "Item {$index}: Invalid data format";
                    continue;
                }

                // Validate and sanitize
                $sanitized = $this->validateAndSanitizePegawaiData($pegawai);
                
                // Skip if no name (required field)
                if (empty($sanitized['nama'])) {
                    $errors[] = "Item {$index}: Missing required field 'nama'";
                    continue;
                }

                $processed[] = $sanitized;
            } catch (Exception $e) {
                $errors[] = "Item {$index}: " . $e->getMessage();
                Log::error("Error processing pegawai item {$index}", [
                    'error' => $e->getMessage(),
                    'data' => $pegawai,
                ]);
            }
        }

        return [
            'processed' => $processed,
            'errors' => $errors,
            'total_processed' => count($processed),
            'total_errors' => count($errors),
        ];
    }

    /**
     * Process dosen data batch
     */
    protected function processDosenBatch(array $dosenList)
    {
        $processed = [];
        $errors = [];

        foreach ($dosenList as $index => $dosen) {
            try {
                if (!is_array($dosen)) {
                    $errors[] = "Item {$index}: Invalid data format";
                    continue;
                }

                // Validate and sanitize
                $sanitized = $this->validateAndSanitizeDosenData($dosen);
                
                // Skip if no name (required field)
                if (empty($sanitized['nama'])) {
                    $errors[] = "Item {$index}: Missing required field 'nama'";
                    continue;
                }

                $processed[] = $sanitized;
            } catch (Exception $e) {
                $errors[] = "Item {$index}: " . $e->getMessage();
                Log::error("Error processing dosen item {$index}", [
                    'error' => $e->getMessage(),
                    'data' => $dosen,
                ]);
            }
        }

        return [
            'processed' => $processed,
            'errors' => $errors,
            'total_processed' => count($processed),
            'total_errors' => count($errors),
        ];
    }

    /**
     * Generate sync summary
     */
    protected function generateSyncSummary(array $pegawaiResult, array $dosenResult)
    {
        return [
            'pegawai' => [
                'total_api' => $pegawaiResult['total_api'] ?? 0,
                'total_processed' => $pegawaiResult['total_processed'] ?? 0,
                'total_inserted' => $pegawaiResult['total_inserted'] ?? 0,
                'total_errors' => $pegawaiResult['total_errors'] ?? 0,
                'errors' => $pegawaiResult['errors'] ?? [],
            ],
            'dosen' => [
                'total_api' => $dosenResult['total_api'] ?? 0,
                'total_processed' => $dosenResult['total_processed'] ?? 0,
                'total_inserted' => $dosenResult['total_inserted'] ?? 0,
                'total_errors' => $dosenResult['total_errors'] ?? 0,
                'errors' => $dosenResult['errors'] ?? [],
            ],
            'timestamp' => now()->toISOString(),
            'duration' => $pegawaiResult['duration'] + ($dosenResult['duration'] ?? 0),
        ];
    }

    /**
     * Log sync results
     */
    protected function logSyncResults(array $summary)
    {
        Log::info('Sevima sync completed', [
            'summary' => $summary,
            'pegawai_count' => $summary['pegawai']['total_inserted'],
            'dosen_count' => $summary['dosen']['total_inserted'],
            'total_errors' => $summary['pegawai']['total_errors'] + $summary['dosen']['total_errors'],
        ]);
    }

    /**
     * Format error message to be user-friendly
     */
    protected function formatUserFriendlyError($e)
    {
        $errorMessage = $e->getMessage();
        
        // Cek untuk error spesifik dan berikan pesan yang lebih user-friendly
        if (strpos($errorMessage, '403') !== false || strpos($errorMessage, 'Forbidden') !== false) {
            return "Akses ditolak (403 Forbidden). Kemungkinan IP address Anda belum di-whitelist oleh server Sevima. Silakan hubungi admin IT untuk menambahkan IP address ke whitelist.";
        }
        
        if (strpos($errorMessage, '401') !== false || strpos($errorMessage, 'Unauthorized') !== false) {
            return "Autentikasi gagal (401 Unauthorized). Periksa kredensial API Sevima Anda.";
        }
        
        if (strpos($errorMessage, '404') !== false || strpos($errorMessage, 'Not Found') !== false) {
            return "Endpoint API tidak ditemukan (404 Not Found). Periksa konfigurasi URL API Sevima.";
        }
        
        if (strpos($errorMessage, '500') !== false || strpos($errorMessage, 'Internal Server Error') !== false) {
            return "Server error (500 Internal Server Error). Terjadi kesalahan di server Sevima. Silakan coba beberapa saat lagi.";
        }
        
        if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'Connection timed out') !== false) {
            return "Koneksi timeout. Server Sevima tidak merespon dalam waktu yang ditentukan. Periksa koneksi internet Anda atau coba lagi nanti.";
        }
        
        if (strpos($errorMessage, 'Connection refused') !== false) {
            return "Koneksi ditolak. Server Sevima tidak dapat dijangkau. Periksa apakah server Sevima sedang online atau ada firewall yang memblokir.";
        }
        
        if (strpos($errorMessage, 'cURL error') !== false) {
            return "Error koneksi cURL: " . $errorMessage . ". Periksa koneksi internet dan konfigurasi jaringan Anda.";
        }
        
        if (strpos($errorMessage, 'SSL certificate') !== false || strpos($errorMessage, 'SSL') !== false) {
            return "Error SSL/TLS: " . $errorMessage . ". Terjadi masalah dengan sertifikat keamanan server Sevima.";
        }
        
        if (strpos($errorMessage, 'Gagal terhubung ke API Sevima') !== false) {
            return "Tidak dapat terhubung ke API Sevima. Periksa koneksi internet dan konfigurasi API.";
        }
        
        // Untuk error lainnya, tampilkan pesan yang lebih umum tapi tetap informatif
        if (empty($errorMessage)) {
            return "Terjadi error yang tidak diketahui saat sinkronisasi data. Silakan coba lagi atau hubungi admin IT.";
        }
        
        // Jika error message terlalu panjang, potong untuk tampilan yang lebih baik
        if (strlen($errorMessage) > 200) {
            return "Error: " . substr($errorMessage, 0, 200) . "...";
        }
        
        return "Error: " . $errorMessage;
    }
}