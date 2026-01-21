<?php
/**
 * Test script untuk verifikasi perubahan mapping PIN2 (ID2)
 * 
 * Script ini untuk menguji apakah perubahan dari PIN biasa ke PIN2 sudah berjalan dengan benar
 */

require_once 'vendor/autoload.php';

use App\Services\FingerprintUserSyncService;
use App\Services\FingerprintService;
use App\Models\MesinFinger;
use Illuminate\Support\Facades\DB;

try {
    // Bootstrap Laravel
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "=== TEST PIN2 MAPPING ===\n\n";

    // 1. Test ambil data user dari mesin
    echo "1. Mengambil data user dari mesin fingerprint...\n";
    
    $machine = MesinFinger::where('status', 'active')->first();
    if (!$machine) {
        echo "ERROR: Tidak ada mesin fingerprint aktif ditemukan!\n";
        exit(1);
    }

    echo "Menggunakan mesin: {$machine->name} ({$machine->ip_address}:{$machine->port})\n";

    $fingerprintService = new FingerprintService($machine->id);
    $machineUsers = $fingerprintService->getUsersFromMachine();

    if (!$machineUsers['success']) {
        echo "ERROR: Gagal mengambil data dari mesin: {$machineUsers['message']}\n";
        exit(1);
    }

    $usersData = $machineUsers['data'] ?? [];
    echo "Berhasil mengambil " . count($usersData) . " user dari mesin\n\n";

    // 2. Tampilkan sample data untuk verifikasi PIN2
    echo "2. Sample data user (5 pertama):\n";
    echo "------------------------------------------------------------\n";
    echo sprintf("%-10s %-20s %-10s %-10s\n", "PIN", "Nama", "PIN2", "Status");
    echo "------------------------------------------------------------\n";

    $sampleCount = min(5, count($usersData));
    for ($i = 0; $i < $sampleCount; $i++) {
        $user = $usersData[$i];
        $pin = $user['pin'] ?? 'N/A';
        $name = $user['name'] ?? 'Unknown';
        $pin2 = $user['pin2'] ?? 'N/A';
        $status = ($pin2 && $pin2 !== '0' && $pin2 !== '') ? 'PIN2 OK' : 'PIN Biasa';
        
        echo sprintf("%-10s %-20s %-10s %-10s\n", $pin, substr($name, 0, 18), $pin2, $status);
    }
    echo "------------------------------------------------------------\n\n";

    // 3. Test sync user dengan PIN2
    echo "3. Test sync user ke database menggunakan PIN2...\n";
    
    $syncService = new FingerprintUserSyncService();
    $syncResult = $syncService->syncUsersFromMachine($machine->id);

    echo "Sync result:\n";
    echo "- Success: " . ($syncResult['success'] ? 'YES' : 'NO') . "\n";
    echo "- Message: {$syncResult['message']}\n";
    echo "- Created: {$syncResult['created']}\n";
    echo "- Updated: {$syncResult['updated']}\n";
    echo "- Failed: {$syncResult['failed']}\n";

    if (!empty($syncResult['errors'])) {
        echo "\nErrors:\n";
        foreach ($syncResult['errors'] as $error) {
            if (is_array($error)) {
                echo "- PIN: {$error['pin']}, PIN2: {$error['pin2']}, Error: {$error['error']}\n";
            } else {
                echo "- $error\n";
            }
        }
    }
    echo "\n";

    // 4. Verifikasi data di database
    echo "4. Verifikasi data di fingerprint_user_mappings table:\n";
    
    $mappings = DB::table('fingerprint_user_mappings')
        ->select('pin', 'name', 'created_at', 'updated_at')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    echo "5 data terakhir di database:\n";
    echo "------------------------------------------------------------\n";
    echo sprintf("%-10s %-20s %-20s %-20s\n", "PIN", "Nama", "Created", "Updated");
    echo "------------------------------------------------------------\n";

    foreach ($mappings as $mapping) {
        echo sprintf("%-10s %-20s %-20s %-20s\n", 
            $mapping->pin, 
            substr($mapping->name, 0, 18),
            $mapping->created_at,
            $mapping->updated_at
        );
    }
    echo "------------------------------------------------------------\n\n";

    // 5. Test method getUserByPin2
    echo "5. Test method getUserByPin2:\n";
    
    if (!empty($usersData)) {
        $testUser = $usersData[0];
        $testPin2 = $testUser['pin2'] ?? $testUser['pin'];
        
        if ($testPin2 && $testPin2 !== '0' && $testPin2 !== '') {
            $userByPin2 = $fingerprintService->getUserByPin2($testPin2);
            echo "Mencari user dengan PIN2: $testPin2\n";
            echo "Result: " . ($userByPin2 ? "Found - {$userByPin2->name}" : "Not found") . "\n";
        } else {
            echo "Sample user tidak memiliki PIN2 yang valid\n";
        }
    }

    echo "\n=== TEST SELESAI ===\n";
    echo "Perubahan mapping PIN ke PIN2 telah diimplementasikan.\n";
    echo "Silakan cek hasil di atas untuk memastikan semua berjalan dengan benar.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
