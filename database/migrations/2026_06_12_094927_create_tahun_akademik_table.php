<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_akademik', function (Blueprint $table) {
            // Menggunakan integer bawaan dengan auto_increment untuk menyamai int(11) di SQL
            $table->integer('id_tahun_akademik', true); 
            $table->string('tahun_masuk', 4);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_akademik');
    }
};