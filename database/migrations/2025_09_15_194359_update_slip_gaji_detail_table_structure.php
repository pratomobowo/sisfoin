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
        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            // Hapus field yang tidak ada dalam dokumentasi terbaru (jika ada)
            if (Schema::hasColumn('slip_gaji_detail', 'nama')) {
                $table->dropColumn('nama');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'tunjangan_dosen_bantuan_kopertis')) {
                $table->dropColumn('tunjangan_dosen_bantuan_kopertis');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'tunjangan_pendidikan_guru_besar')) {
                $table->dropColumn('tunjangan_pendidikan_guru_besar');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'pajak_per_bulan')) {
                $table->dropColumn('pajak_per_bulan');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'raihan_s2s3')) {
                $table->dropColumn('raihan_s2s3');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'raihan_pendidikan')) {
                $table->dropColumn('raihan_pendidikan');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'potongan_infaq_masjid')) {
                $table->dropColumn('potongan_infaq_masjid');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'potongan_dplk')) {
                $table->dropColumn('potongan_dplk');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'potongan_dana_musibah')) {
                $table->dropColumn('potongan_dana_musibah');
            }

            // Tambahkan field-field yang ada dalam dokumentasi tapi belum ada di tabel
            if (! Schema::hasColumn('slip_gaji_detail', 'tunjangan_kemahalan')) {
                $table->decimal('tunjangan_kemahalan', 15, 2)->nullable()->after('tunjangan_keluarga');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'tunjangan_kenaikan_mahasiswa')) {
                $table->decimal('tunjangan_kenaikan_mahasiswa', 15, 2)->nullable()->after('tunjangan_kesehatan');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'masa_kerja')) {
                $table->decimal('masa_kerja', 15, 2)->nullable()->after('tunjangan_golongan');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'tarif')) {
                $table->decimal('tarif', 15, 2)->nullable()->after('masa_kerja');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'kehadiran')) {
                $table->decimal('kehadiran', 15, 2)->nullable()->after('tarif');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'transport')) {
                $table->decimal('transport', 15, 2)->nullable()->after('kehadiran');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'tunjangan_jabatan')) {
                $table->decimal('tunjangan_jabatan', 15, 2)->nullable()->after('tunjangan_rumah');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'tunjangan_pendidikan')) {
                $table->decimal('tunjangan_pendidikan', 15, 2)->nullable()->after('tunjangan_jabatan');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'honor_tetap')) {
                $table->decimal('honor_tetap', 15, 2)->nullable()->after('tunjangan_pendidikan');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'insentif_golongan')) {
                $table->decimal('insentif_golongan', 15, 2)->nullable()->after('honor_tetap');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'beban_manajemen')) {
                $table->decimal('beban_manajemen', 15, 2)->nullable()->after('tunjangan_struktural');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'honor_tunai')) {
                $table->decimal('honor_tunai', 15, 2)->nullable()->after('beban_manajemen');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_bmt')) {
                $table->decimal('potongan_bmt', 15, 2)->nullable()->after('potongan_koperasi');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_dana_musibah')) {
                $table->decimal('potongan_dana_musibah', 15, 2)->nullable()->after('potongan_bmt');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_lazmaal')) {
                $table->decimal('potongan_lazmaal', 15, 2)->nullable()->after('potongan_dana_musibah');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_infaq_masjid')) {
                $table->decimal('potongan_infaq_masjid', 15, 2)->nullable()->after('potongan_lazmaal');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_dplk')) {
                $table->decimal('potongan_dplk', 15, 2)->nullable()->after('potongan_bpjs_ketenagakerjaan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            // Hapus field-field yang ditambahkan
            if (Schema::hasColumn('slip_gaji_detail', 'tunjangan_kemahalan')) {
                $table->dropColumn('tunjangan_kemahalan');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'tunjangan_kenaikan_mahasiswa')) {
                $table->dropColumn('tunjangan_kenaikan_mahasiswa');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'masa_kerja')) {
                $table->dropColumn('masa_kerja');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'tarif')) {
                $table->dropColumn('tarif');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'kehadiran')) {
                $table->dropColumn('kehadiran');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'transport')) {
                $table->dropColumn('transport');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'tunjangan_jabatan')) {
                $table->dropColumn('tunjangan_jabatan');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'tunjangan_pendidikan')) {
                $table->dropColumn('tunjangan_pendidikan');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'honor_tetap')) {
                $table->dropColumn('honor_tetap');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'insentif_golongan')) {
                $table->dropColumn('insentif_golongan');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'beban_manajemen')) {
                $table->dropColumn('beban_manajemen');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'honor_tunai')) {
                $table->dropColumn('honor_tunai');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'potongan_bmt')) {
                $table->dropColumn('potongan_bmt');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'potongan_dana_musibah')) {
                $table->dropColumn('potongan_dana_musibah');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'potongan_lazmaal')) {
                $table->dropColumn('potongan_lazmaal');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'potongan_infaq_masjid')) {
                $table->dropColumn('potongan_infaq_masjid');
            }

            if (Schema::hasColumn('slip_gaji_detail', 'potongan_dplk')) {
                $table->dropColumn('potongan_dplk');
            }

            // Tambahkan kembali field yang dihapus (jika ada)
            if (! Schema::hasColumn('slip_gaji_detail', 'nama')) {
                $table->string('nama', 255)->nullable();
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'tunjangan_dosen_bantuan_kopertis')) {
                $table->decimal('tunjangan_dosen_bantuan_kopertis', 15, 2)->nullable();
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'tunjangan_pendidikan_guru_besar')) {
                $table->decimal('tunjangan_pendidikan_guru_besar', 15, 2)->nullable();
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'pajak_per_bulan')) {
                $table->decimal('pajak_per_bulan', 15, 2)->nullable();
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'raihan_s2s3')) {
                $table->string('raihan_s2s3', 10)->nullable();
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'raihan_pendidikan')) {
                $table->decimal('raihan_pendidikan', 15, 2)->nullable();
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_infaq_masjid')) {
                $table->decimal('potongan_infaq_masjid', 15, 2)->nullable();
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_dplk')) {
                $table->decimal('potongan_dplk', 15, 2)->nullable();
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_dana_musibah')) {
                $table->decimal('potongan_dana_musibah', 15, 2)->nullable();
            }
        });
    }
};
