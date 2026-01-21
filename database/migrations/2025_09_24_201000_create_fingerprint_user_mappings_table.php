<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fingerprint_user_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('pin')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('pin');
            $table->index('is_active');
        });

        // Insert some dummy data for testing
        \DB::table('fingerprint_user_mappings')->insert([
            [
                'pin' => '001',
                'name' => 'Ahmad Dahlan',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pin' => '002', 
                'name' => 'Siti Aisyah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pin' => '003',
                'name' => 'Budi Santoso',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pin' => '004',
                'name' => 'Dewi Lestari',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pin' => '005',
                'name' => 'Eko Prasetyo',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pin' => '006',
                'name' => 'Fitri Handayani',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pin' => '007',
                'name' => 'Gunawan Wijaya',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pin' => '008',
                'name' => 'Hana Pertiwi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pin' => '009',
                'name' => 'Irfan Hakim',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pin' => '010',
                'name' => 'Julia Rahmawati',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fingerprint_user_mappings');
    }
};
