<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asal_sekolah', function (Blueprint $table) {
            $table->integer('id_asal_sekolah', true);
            $table->string('nim', 20)->nullable();
            $table->enum('pendidikan_asal', ['SMA', 'SMK', 'MA'])->nullable();
            $table->integer('id_prov')->nullable();
            $table->integer('id_kota')->nullable();
            $table->string('nama_sekolah', 150)->nullable();
            $table->text('alamat_sekolah')->nullable();
            $table->string('no_telepon', 20)->nullable();

            // Foreign Key
            $table->foreign('nim')
                  ->references('nim')->on('mahasiswa')
                  ->onDelete('cascade'); // Jika mahasiswa dihapus, asal sekolah ikut terhapus
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asal_sekolah');
    }
};