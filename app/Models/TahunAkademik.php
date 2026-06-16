<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    use HasFactory;

    protected $table = 'tahun_akademik';
    protected $primaryKey = 'id_tahun_akademik';
    public $timestamps = false;

    protected $fillable = [
        'tahun_masuk'
    ];

    /**
     * Relasi ke data kumpulan Mahasiswa yang terdaftar di tahun ini
     */
    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'id_tahun_akademik', 'id_tahun_akademik');
    }
}