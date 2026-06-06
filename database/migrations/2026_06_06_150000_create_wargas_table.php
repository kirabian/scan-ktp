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
        Schema::create('wargas', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique()->index();
            $table->string('nama');
            $table->string('tempat_tgl_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->text('alamat_ktp');
            $table->string('rt_rw_ktp')->nullable();
            $table->string('kel_desa_ktp')->nullable();
            $table->string('kecamatan_ktp')->nullable();
            $table->boolean('is_domisili_sesuai_ktp')->default(true);
            $table->string('provinsi_domisili')->nullable();
            $table->string('kota_kab_domisili')->nullable();
            $table->string('kecamatan_domisili')->nullable();
            $table->string('kel_desa_domisili')->nullable();
            $table->text('alamat_detail_domisili')->nullable();
            $table->string('kode_pos_domisili')->nullable();
            $table->string('no_wa_hp');
            $table->string('pekerjaan');
            $table->string('foto_ktp_path');
            $table->string('foto_wajah_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wargas');
    }
};
