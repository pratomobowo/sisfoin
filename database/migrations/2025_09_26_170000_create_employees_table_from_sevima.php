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
        Schema::create('employees', function (Blueprint $table) {
            // Field unik yang bukan dari data Sevima
            $table->id();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Field-field Sevima (semua dalam format varchar)
            $table->string('id_pegawai')->nullable();
            $table->string('nip')->nullable();
            $table->string('nip_pns')->nullable();
            $table->string('nidn')->nullable();
            $table->string('nup')->nullable();
            $table->string('nidk')->nullable();
            $table->string('nupn')->nullable();
            $table->string('nik')->nullable();
            $table->string('nama')->nullable();
            $table->string('gelar_depan')->nullable();
            $table->string('gelar_belakang')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('id_agama')->nullable();
            $table->string('agama')->nullable();
            $table->string('id_kewarganegaraan')->nullable();
            $table->string('kewarganegaraan')->nullable();
            $table->string('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('status_nikah')->nullable();
            $table->text('alamat_domisili')->nullable();
            $table->string('rt_domisili')->nullable();
            $table->string('rw_domisili')->nullable();
            $table->string('kode_pos_domisili')->nullable();
            $table->string('id_kecamatan_domisili')->nullable();
            $table->string('kecamatan_domisili')->nullable();
            $table->string('id_kota_domisili')->nullable();
            $table->string('kota_domisili')->nullable();
            $table->string('id_provinsi_domisili')->nullable();
            $table->string('provinsi_domisili')->nullable();
            $table->text('alamat_ktp')->nullable();
            $table->string('rt_ktp')->nullable();
            $table->string('rw_ktp')->nullable();
            $table->string('kode_pos_ktp')->nullable();
            $table->string('id_kecamatan_ktp')->nullable();
            $table->string('kecamatan_ktp')->nullable();
            $table->string('id_kota_ktp')->nullable();
            $table->string('kota_ktp')->nullable();
            $table->string('id_provinsi_ktp')->nullable();
            $table->string('provinsi_ktp')->nullable();
            $table->string('nomor_hp')->nullable();
            $table->string('email')->nullable();
            $table->string('email_kampus')->nullable();
            $table->string('id_satuan_kerja')->nullable();
            $table->string('satuan_kerja')->nullable();
            $table->string('id_home_base')->nullable();
            $table->string('home_base')->nullable();
            $table->string('telepon')->nullable();
            $table->string('telepon_kantor')->nullable();
            $table->string('telepon_alternatif')->nullable();
            $table->string('id_pendidikan_terakhir')->nullable();
            $table->string('tanggal_masuk')->nullable();
            $table->string('tanggal_sertifikasi_dosen')->nullable();
            $table->string('id_status_aktif')->nullable();
            $table->string('status_aktif')->nullable();
            $table->string('id_status_kepegawaian')->nullable();
            $table->string('status_kepegawaian')->nullable();
            $table->string('id_pangkat')->nullable();
            $table->string('id_jabatan_fungsional')->nullable();
            $table->string('jabatan_fungsional')->nullable();
            $table->string('id_jabatan_sub_fungsional')->nullable();
            $table->string('jabatan_sub_fungsional')->nullable();
            $table->string('id_jabatan_struktural')->nullable();
            $table->string('jabatan_struktural')->nullable();
            $table->string('is_deleted')->nullable();
            $table->string('id_sso')->nullable();
            
            // Menambahkan indeks untuk field-field penting
            $table->index('nip');
            $table->index('id_pegawai');
            $table->index('nama');
            $table->index('id_satuan_kerja');
            $table->index('status_aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};