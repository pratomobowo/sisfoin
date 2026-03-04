<?php
$names = ['Dede Ardri', 'Ayu Septia', 'Irnal Zainal'];
foreach($names as $name) {
    $emp = \App\Models\Employee::withTrashed()->where('nama', 'like', '%' . $name . '%')->first();
    if($emp) {
        $status = $emp->trashed() ? 'Yes ('.$emp->deleted_at.')' : 'No';
        echo $emp->nama . ' - Deleted: ' . $status . " - Status Aktif: " . $emp->status_aktif . "\n";
    } else {
        echo "Not found: $name\n";
    }
}
