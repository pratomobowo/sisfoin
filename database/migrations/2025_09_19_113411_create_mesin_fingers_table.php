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
        Schema::create('mesin_fingers', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mesin');
            $table->string('ip_address');
            $table->integer('port')->default(4370);
            $table->string('lokasi')->nullable();
            $table->string('status')->default('inactive'); // active, inactive, error
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesin_fingers');
    }
};
