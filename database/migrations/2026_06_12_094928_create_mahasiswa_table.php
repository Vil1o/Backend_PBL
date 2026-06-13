<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->string('nim', 20)->primary();
            $table->string('nama_mhs', 150);
            $table->integer('id_tahun_akademik')->nullable(); // Tipe harus sama dengan id_tahun_akademik di atas
            $table->tinyInteger('id_prodi');
            $table->text('alamat')->nullable();
            $table->integer('id_kategori')->nullable();
            $table->integer('id_beasiswa')->nullable();

            // Foreign Key
            $table->foreign('id_tahun_akademik')
                  ->references('id_tahun_akademik')->on('tahun_akademik')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};