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
        Schema::create('surat_keputusan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->string('tipe_surat');
            $table->string('kategori_sk');
            $table->string('tentang');
            $table->date('tanggal_penetapan');
            $table->date('tanggal_berlaku');
            $table->string('ditandatangani_oleh');
            $table->text('deskripsi')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size')->unsigned();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index('nomor_surat');
            $table->index('tipe_surat');
            $table->index('kategori_sk');
            $table->index('tanggal_penetapan');
            $table->index('tanggal_berlaku');
            $table->index('ditandatangani_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keputusan');
    }
};
