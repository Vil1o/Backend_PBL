<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->integer('id_orang_tua', true);
            $table->string('nim', 20)->nullable();
            $table->enum('hubungan', ['ayah', 'ibu', 'wali'])->nullable();
            $table->string('nama_lengkap', 150)->nullable();
            $table->string('nik', 20)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('status_hidup', 50)->nullable();
            $table->string('status_kekerabatan', 50)->nullable();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->enum('penghasilan', [
                'Kurang dari sama dengan 1.000.000', 
                '1.000.000-2.000.000', 
                '2.000.000-4.000.000', 
                '4.000.000-6.000.000', 
                'Lebih dari 6.000.000'
            ])->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->string('email', 100)->nullable();

            // Foreign Key
            $table->foreign('nim')
                  ->references('nim')->on('mahasiswa')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_tua');
    }
};