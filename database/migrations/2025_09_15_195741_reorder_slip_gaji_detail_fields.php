<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Karena MySQL tidak mendukung reorder column secara langsung,
        // kita akan membiarkan struktur tabel apa adanya
        // Field-field sudah sesuai dengan dokumentasi
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
