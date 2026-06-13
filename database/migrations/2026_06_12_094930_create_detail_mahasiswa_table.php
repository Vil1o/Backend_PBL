<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_mahasiswa', function (Blueprint $table) {
            $table->integer('id_detail', true);
            $table->string('nim', 20); // Not null di file SQL
            $table->integer('id_kelas')->nullable();
            $table->string('kebutuhan_khusus', 100)->nullable();
            $table->boolean('biodata_valid')->nullable(); // boolean setara dengan tinyint(1)
            $table->enum('jk', ['L', 'P'])->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->string('agama', 50)->nullable();
            $table->decimal('bb', 5, 2)->nullable();
            $table->decimal('tb', 5, 2)->nullable();
            $table->string('gol_darah', 3)->nullable();
            $table->string('transportasi', 50)->nullable();
            $table->string('kewarganegaraan', 50)->nullable();
            $table->string('nik', 20)->nullable();
            $table->string('no_kk', 20)->nullable();
            $table->string('status_nikah', 20)->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->string('instansi_pekerjaan', 100)->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->string('email_kampus', 100)->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('desa', 100)->nullable();
            $table->string('status_tinggal', 50)->nullable();
            $table->integer('id_prov')->nullable();
            $table->integer('id_kota')->nullable();
            $table->integer('id_kodepos')->nullable();
            $table->integer('id_status')->nullable();
            $table->integer('id_ortu')->nullable();
            $table->integer('id_asal_sekolah')->nullable();

            // Foreign Key
            $table->foreign('nim')
                  ->references('nim')->on('mahasiswa')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_mahasiswa');
    }
};