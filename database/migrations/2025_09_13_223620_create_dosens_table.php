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
        Schema::create('dosens', function (Blueprint $table) {
            $table->id();

            // Identitas dasar
            $table->string('id_pegawai', 20)->nullable();
            $table->string('nip', 50)->nullable();
            $table->string('nip_pns', 50)->nullable();
            $table->string('nidn', 50)->nullable();
            $table->string('nup', 50)->nullable();
            $table->string('nidk', 50)->nullable();
            $table->string('nupn', 50)->nullable();
            $table->string('nik', 50)->nullable();
            $table->string('nama')->nullable();
            $table->string('gelar_depan')->nullable();
            $table->string('gelar_belakang')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();

            // Agama dan kewarganegaraan
            $table->integer('id_agama')->nullable();
            $table->string('agama')->nullable();
            $table->string('id_kewarganegaraan', 10)->nullable();
            $table->string('kewarganegaraan')->nullable();

            // Data kelahiran
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('status_nikah', 10)->nullable();

            // Alamat domisili
            $table->text('alamat_domisili')->nullable();
            $table->string('rt_domisili', 10)->nullable();
            $table->string('rw_domisili', 10)->nullable();
            $table->string('kode_pos_domisili', 10)->nullable();
            $table->integer('id_kecamatan_domisili')->nullable();
            $table->string('kecamatan_domisili')->nullable();
            $table->integer('id_kota_domisili')->nullable();
            $table->string('kota_domisili')->nullable();
            $table->integer('id_provinsi_domisili')->nullable();
            $table->string('provinsi_domisili')->nullable();

            // Alamat KTP
            $table->text('alamat_ktp')->nullable();
            $table->string('rt_ktp', 10)->nullable();
            $table->string('rw_ktp', 10)->nullable();
            $table->string('kode_pos_ktp', 10)->nullable();
            $table->integer('id_kecamatan_ktp')->nullable();
            $table->string('kecamatan_ktp')->nullable();
            $table->integer('id_kota_ktp')->nullable();
            $table->string('kota_ktp')->nullable();
            $table->integer('id_provinsi_ktp')->nullable();
            $table->string('provinsi_ktp')->nullable();

            // Kontak
            $table->string('nomor_hp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('email_kampus')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('telepon_kantor', 20)->nullable();
            $table->string('telepon_alternatif', 20)->nullable();

            // Unit kerja
            $table->integer('id_satuan_kerja')->nullable();
            $table->string('satuan_kerja')->nullable();
            $table->integer('id_home_base')->nullable();
            $table->string('home_base')->nullable();
            $table->integer('id_pendidikan_terakhir')->nullable();

            // Data kepegawaian
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_sertifikasi_dosen')->nullable();
            $table->string('id_status_aktif', 10)->nullable();
            $table->string('status_aktif', 50)->nullable();
            $table->string('id_status_kepegawaian', 10)->nullable();
            $table->string('status_kepegawaian', 100)->nullable();
            $table->string('id_pangkat', 10)->nullable();
            $table->string('id_jabatan_fungsional', 10)->nullable();
            $table->string('jabatan_fungsional', 100)->nullable();
            $table->string('id_jabatan_sub_fungsional', 10)->nullable();
            $table->string('jabatan_sub_fungsional', 100)->nullable();
            $table->string('id_jabatan_struktural', 10)->nullable();
            $table->string('jabatan_struktural', 100)->nullable();

            // Data sistem
            $table->boolean('is_deleted')->default(false);
            $table->integer('id_sso')->nullable();
            $table->timestamp('api_created_at')->nullable();
            $table->timestamp('api_updated_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('nip');
            $table->index('nidn');
            $table->index('nama');
            $table->index('status_aktif');
            $table->index('satuan_kerja');
            $table->index('jabatan_fungsional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};
