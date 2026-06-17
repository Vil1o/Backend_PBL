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
        Schema::create('detail_mahasiswa', function (Blueprint $table) {
            // Primary Key
            $table->id('id_detail'); // int(11) AUTO_INCREMENT

            // Foreign Key NIM (tidak boleh null karena data utama)
            $table->string('nim', 20);

            // Kolom Lainnya (Semua di-set Nullable sesuai gambar phpMyAdmin)
            $table->integer('id_kelas')->nullable();
            $table->string('kebutuhan_khusus', 20)->nullable();
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
            $table->string('email_pribadi', 100)->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('status_tinggal', 50)->nullable();
            $table->integer('id_prov')->nullable();
            $table->integer('id_kota')->nullable();

            // Kolom Foreign Key Baru (Nullable agar mahasiswa bisa isi bertahap)
            // Menggunakan unsignedBigInteger/unsignedInteger menyesuaikan tipe PK tabel asal
            $table->unsignedBigInteger('id_orang_tua')->nullable();
            $table->unsignedBigInteger('id_asal_sekolah')->nullable();

            // Peresmian Hubungan Foreign Key di Database           
            // Tambahkan ini jika menggunakan fitur bawaan timestamps Laravel
            // $table->timestamps(); 
        });
        Schema::table('detail_mahasiswa', function (Blueprint $table) {
            // Foreign Key ke tabel mahasiswa
            $table->foreign('nim')
                  ->references('nim')->on('mahasiswa')
                  ->onDelete('cascade'); // Jika mahasiswa dihapus, detail mahasiswa ikut terhapus

            // Foreign Key ke tabel orang_tua
            $table->foreign('id_orang_tua')
                  ->references('id_orang_tua')->on('orang_tua')
                  ->onDelete('set null'); // Jika orang tua dihapus, set null

            // Foreign Key ke tabel asal_sekolah
            $table->foreign('id_asal_sekolah')
                  ->references('id_asal_sekolah')->on('asal_sekolah')
                  ->onDelete('set null'); // Jika asal sekolah dihapus, set null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_mahasiswa');
    }
};