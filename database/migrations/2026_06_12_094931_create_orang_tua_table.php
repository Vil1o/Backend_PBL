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
        Schema::create('orang_tua', function (Blueprint $table) {
            // 1. Primary Key
            $table->id('id_orang_tua'); // Menghasilkan int(11) AUTO_INCREMENT

            // 2. Foreign Key ke tabel mahasiswa
            $table->string('nim', 20)->nullable(); 

            // 3. Data Ayah
            $table->string('nama_ayah', 150)->nullable();
            $table->string('nik_ayah', 20)->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->string('status_hidup_ayah', 50)->nullable();
            $table->string('status_kekerabatan_ayah', 50)->nullable();
            $table->string('pendidikan_terakhir_ayah', 50)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->enum('penghasilan_ayah', [
                'Kurang dari sama dengan 1.000.000',
                '1.000.000-2.000.000',
                '2.000.000-4.000.000',
                '4.000.000-6.000.000',
                'Lebih dari 6.000.000'
            ])->nullable();
            $table->string('no_telepon_ayah', 20)->nullable();

            // 4. Data Ibu
            $table->string('nama_ibu', 150)->nullable();
            $table->string('nik_ibu', 20)->nullable();
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('status_hidup_ibu', 50)->nullable();
            $table->string('status_kekerabatan_ibu', 50)->nullable();
            $table->string('pendidikan_terakhir_ibu', 50)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->enum('penghasilan_ibu', [
                'Kurang dari sama dengan 1.000.000',
                '1.000.000-2.000.000',
                '2.000.000-4.000.000',
                '4.000.000-6.000.000',
                'Lebih dari 6.000.000'
            ])->nullable();
            $table->string('no_telepon_ibu', 20)->nullable();

            // 5. Data Umum
            $table->text('alamat')->nullable();
            $table->string('email', 100)->nullable();

            // Opsional: Relasi Foreign Key (Hapus komentar di bawah jika tabel mahasiswa sudah ada)
            // $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orang_tua');
    }
};