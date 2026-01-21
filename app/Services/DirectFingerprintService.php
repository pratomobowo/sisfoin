<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DirectFingerprintService
{
    /**
     * Parse data dari response XML mesin fingerprint
     */
    private function parseData($data, $p1, $p2)
    {
        $data = ' '.$data;
        $hasil = '';
        $awal = strpos($data, $p1);
        if ($awal != '') {
            $akhir = strpos(strstr($data, $p1), $p2);
            if ($akhir != '') {
                $hasil = substr($data, $awal + strlen($p1), $akhir - strlen($p1));
            }
        }

        return $hasil;
    }

    /**
     * Test koneksi ke mesin fingerprint menggunakan metode direct HTTP
     */
    public function testConnection($ip, $port = 80)
    {
        try {
            $connect = @fsockopen($ip, $port, $errno, $errstr, 5);

            if (! $connect) {
                return [
                    'success' => false,
                    'message' => "Koneksi gagal! Tidak dapat terhubung ke {$ip}:{$port}. Error: {$errstr}",
                    'error_code' => $errno,
                ];
            }

            fclose($connect);

            return [
                'success' => true,
                'message' => "Koneksi berhasil! Mesin dapat dijangkau pada {$ip}:{$port}",
                'method' => 'Direct HTTP',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error saat test koneksi: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get attendance logs dari mesin fingerprint menggunakan metode direct HTTP
     */
    public function getAttendanceLogs($ip, $port = 80, $key = '0')
    {
        try {
            Log::info("Mengambil data absensi dari mesin {$ip}:{$port} menggunakan metode Direct HTTP");

            $connect = @fsockopen($ip, $port, $errno, $errstr, 10);

            if (! $connect) {
                throw new \Exception("Tidak dapat terhubung ke mesin fingerprint: {$errstr}");
            }

            $soap_request = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><SOAP-ENV:Body><GetAttLog><ArgComKey xsi:type="xsd:integer">'.$key.'</ArgComKey><Arg><PIN xsi:type="xsd:integer">All</PIN></Arg></GetAttLog></SOAP-ENV:Body></SOAP-ENV:Envelope>';
            $newLine = "\r\n";

            fwrite($connect, 'POST /iWsService HTTP/1.0'.$newLine);
            fwrite($connect, 'Content-Type: text/xml'.$newLine);
            fwrite($connect, 'Content-Length: '.strlen($soap_request).$newLine.$newLine);
            fwrite($connect, $soap_request.$newLine);

            $buffer = '';
            while ($response = fgets($connect, 1024)) {
                $buffer = $buffer.$response;
            }

            fclose($connect);

            if (empty($buffer)) {
                throw new \Exception('Tidak ada response dari mesin');
            }

            // Parse response
            $buffer = $this->parseData($buffer, '<GetAttLogResponse>', '</GetAttLogResponse>');
            $buffer = explode("\r\n", $buffer);

            $attendanceData = [];

            for ($a = 1; $a < count($buffer) - 1; $a++) {
                $data = $this->parseData($buffer[$a], '<Row>', '</Row>');
                $pin = $this->parseData($data, '<PIN>', '</PIN>');
                $name = $this->parseData($data, '<Name>', '</Name>');
                $dateTime = $this->parseData($data, '<DateTime>', '</DateTime>');
                $verified = $this->parseData($data, '<Verified>', '</Verified>');
                $status = $this->parseData($data, '<Status>', '</Status>');

                if ($pin) { // Only add if we have valid data
                    $attendanceData[] = [
                        'pin' => $pin,
                        'name' => $name,
                        'datetime' => $dateTime,
                        'verified' => $verified,
                        'status' => $status,
                        'raw_data' => $data,
                    ];
                }
            }

            Log::info('Berhasil mengambil '.count($attendanceData)." record absensi dari mesin {$ip}");

            return [
                'success' => true,
                'message' => 'Data absensi berhasil diambil',
                'count' => count($attendanceData),
                'data' => $attendanceData,
                'method' => 'Direct HTTP',
            ];

        } catch (\Exception $e) {
            Log::error('Error mengambil data absensi via Direct HTTP: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data absensi: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Get user info dari mesin fingerprint
     */
    public function getUserInfo($ip, $port = 80, $key = '0')
    {
        try {
            Log::info("Mengambil data user dari mesin {$ip}:{$port} menggunakan metode Direct HTTP");

            $connect = @fsockopen($ip, $port, $errno, $errstr, 10);

            if (! $connect) {
                throw new \Exception("Tidak dapat terhubung ke mesin fingerprint: {$errstr}");
            }

            $soap_request = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><SOAP-ENV:Body><GetUserInfo><ArgComKey xsi:type="xsd:integer">'.$key.'</ArgComKey><Arg><PIN xsi:type="xsd:integer">All</PIN></Arg></GetUserInfo></SOAP-ENV:Body></SOAP-ENV:Envelope>';
            $newLine = "\r\n";

            fwrite($connect, 'POST /iWsService HTTP/1.0'.$newLine);
            fwrite($connect, 'Content-Type: text/xml'.$newLine);
            fwrite($connect, 'Content-Length: '.strlen($soap_request).$newLine.$newLine);
            fwrite($connect, $soap_request.$newLine);

            $buffer = '';
            while ($response = fgets($connect, 1024)) {
                $buffer = $buffer.$response;
            }

            fclose($connect);

            if (empty($buffer)) {
                throw new \Exception('Tidak ada response dari mesin');
            }

            // Parse response
            $buffer = $this->parseData($buffer, '<GetUserInfoResponse>', '</GetUserInfoResponse>');
            $buffer = explode("\r\n", $buffer);

            $userData = [];

            for ($a = 1; $a < count($buffer) - 1; $a++) {
                $data = $this->parseData($buffer[$a], '<Row>', '</Row>');
                $pin = $this->parseData($data, '<PIN>', '</PIN>');
                $name = $this->parseData($data, '<Name>', '</Name>');
                $password = $this->parseData($data, '<Password>', '</Password>');
                $group = $this->parseData($data, '<Group>', '</Group>');
                $privilege = $this->parseData($data, '<Privilege>', '</Privilege>');
                $card = $this->parseData($data, '<Card>', '</Card>');

                if ($pin) { // Only add if we have valid data
                    $userData[] = [
                        'pin' => $pin,
                        'name' => $name,
                        'password' => $password,
                        'group' => $group,
                        'privilege' => $privilege,
                        'card' => $card,
                        'raw_data' => $data,
                    ];
                }
            }

            Log::info('Berhasil mengambil '.count($userData)." data user dari mesin {$ip}");

            return [
                'success' => true,
                'message' => 'Data user berhasil diambil',
                'count' => count($userData),
                'data' => $userData,
                'method' => 'Direct HTTP',
            ];

        } catch (\Exception $e) {
            Log::error('Error mengambil data user via Direct HTTP: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data user: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Clear attendance logs dari mesin fingerprint
     */
    public function clearAttendanceLogs($ip, $port = 80, $key = '0')
    {
        try {
            Log::info("Menghapus attendance logs dari mesin {$ip}:{$port}");

            $connect = @fsockopen($ip, $port, $errno, $errstr, 10);

            if (! $connect) {
                throw new \Exception("Tidak dapat terhubung ke mesin fingerprint: {$errstr}");
            }

            $soap_request = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><SOAP-ENV:Body><ClearData><ArgComKey xsi:type="xsd:integer">'.$key.'</ArgComKey><Arg><Value xsi:type="xsd:integer">3</Value></Arg></ClearData></SOAP-ENV:Body></SOAP-ENV:Envelope>';
            $newLine = "\r\n";

            fwrite($connect, 'POST /iWsService HTTP/1.0'.$newLine);
            fwrite($connect, 'Content-Type: text/xml'.$newLine);
            fwrite($connect, 'Content-Length: '.strlen($soap_request).$newLine.$newLine);
            fwrite($connect, $soap_request.$newLine);

            $buffer = '';
            while ($response = fgets($connect, 1024)) {
                $buffer = $buffer.$response;
            }

            fclose($connect);

            // Check if operation was successful
            if (strpos($buffer, 'ClearDataResponse') !== false) {
                Log::info("Attendance logs berhasil dihapus dari mesin {$ip}");

                return [
                    'success' => true,
                    'message' => 'Attendance logs berhasil dihapus dari mesin',
                    'method' => 'Direct HTTP',
                ];
            } else {
                throw new \Exception('Gagal menghapus attendance logs');
            }

        } catch (\Exception $e) {
            Log::error('Error menghapus attendance logs: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus attendance logs: '.$e->getMessage(),
            ];
        }
    }
}
