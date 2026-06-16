<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsalSekolah extends Model
{
    use HasFactory;

    protected $table = 'asal_sekolah';
    protected $primaryKey = 'id_asal_sekolah';
    public $timestamps = false;

    protected $fillable = [
        'nim',
        'pendidikan_asal',
        'id_prov',
        'id_kota',
        'nama_sekolah',
        'alamat_sekolah',
        'no_telepon'
    ];

    /**
     * Relasi balik ke Mahasiswa
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function detailMahasiswa()
    {
        return $this->hasMany(DetailMahasiswa::class, 'id_asal_sekolah', 'id_asal_sekolah');
    }
}