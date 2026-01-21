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
        Schema::create('slip_gaji_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->enum('status', ['KARYAWAN_TETAP', 'KARYAWAN_KONTRAK', 'DOSEN_TETAP', 'DOSEN_DPK', 'DOSEN_GURU_BESAR']);
            $table->string('nip', 50);
            $table->string('nama', 255);

            // Komponen Gaji
            $table->decimal('gaji_pokok', 15, 2)->nullable();
            $table->decimal('honor_tetap', 15, 2)->nullable();
            $table->decimal('tpp', 15, 2)->nullable();
            $table->decimal('insentif_golongan', 15, 2)->nullable();
            $table->decimal('tunjangan_keluarga', 15, 2)->nullable();
            $table->decimal('tunjangan_kenaikan_mahasiswa', 15, 2)->nullable();
            $table->decimal('tunjangan_golongan', 15, 2)->nullable();
            $table->decimal('tunjangan_kesehatan', 15, 2)->nullable();
            $table->decimal('tunjangan_rumah', 15, 2)->nullable();
            $table->decimal('tunjangan_struktural', 15, 2)->nullable();
            $table->decimal('tunjangan_fungsional', 15, 2)->nullable();
            $table->string('raihan_s2s3', 10)->nullable();
            $table->decimal('beban_manajemen', 15, 2)->nullable();
            $table->decimal('honor_tunai', 15, 2)->nullable();

            // Penerimaan
            $table->decimal('penerimaan_kotor', 15, 2)->nullable();

            // Potongan
            $table->decimal('potongan_arisan', 15, 2)->nullable();
            $table->decimal('potongan_koperasi', 15, 2)->nullable();
            $table->decimal('potongan_infaq_masjid', 15, 2)->nullable();
            $table->decimal('potongan_bmt', 15, 2)->nullable();
            $table->decimal('potongan_upz', 15, 2)->nullable();
            $table->decimal('potongan_lazmaal', 15, 2)->nullable();
            $table->decimal('potongan_bpjs_kesehatan', 15, 2)->nullable();
            $table->decimal('potongan_bpjs_ketenagakerjaan', 15, 2)->nullable();
            $table->decimal('potongan_dplk', 15, 2)->nullable();
            $table->decimal('potongan_dana_musibah', 15, 2)->nullable();
            $table->decimal('potongan_bkd', 15, 2)->nullable();
            $table->decimal('pajak', 15, 2)->nullable();
            $table->decimal('pph21_terhutang', 15, 2)->nullable();
            $table->decimal('pph21_sudah_dipotong', 15, 2)->nullable();
            $table->decimal('pph21_kurang_dipotong', 15, 2)->nullable();

            // Output
            $table->decimal('gaji_bersih', 15, 2);

            $table->timestamps();

            $table->foreign('header_id')->references('id')->on('slip_gaji_header')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slip_gaji_detail');
    }
};
