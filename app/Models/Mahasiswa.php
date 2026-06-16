<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'mahasiswa';

    // Menentukan primary key custom
    protected $primaryKey = 'nim';

    // Beritahu Laravel bahwa primary key bukan integer auto-increment
    public $incrementing = false;

    // Tipe data primary key
    protected $keyType = 'string';

    // Mematikan fitur timestamp bawaan Laravel
    public $timestamps = false;

    // Kolom yang diizinkan untuk mass-assignment
    protected $fillable = [
        'nim',
        'nama_mhs',
        'id_tahun_akademik',
        'id_prodi',
        'alamat',
        'id_kategori',
        'id_beasiswa',
        'email_kampus'
    ];

    /**
     * Relasi ke DetailMahasiswa (1 to 1)
     */
    public function detail()
    {
        return $this->hasOne(DetailMahasiswa::class, 'nim', 'nim');
    }

    /**
     * Relasi ke OrangTua (1 to Many)
     * Karena satu mahasiswa bisa memiliki data Ayah, Ibu, atau Wali terpisah
     */
    public function orangTua()
    {
        return $this->hasMany(OrangTua::class, 'nim', 'nim');
    }

    /**
     * Relasi ke AsalSekolah (1 to 1)
     */
    public function asalSekolah()
    {
        return $this->hasOne(AsalSekolah::class, 'nim', 'nim');
    }

    /**
     * Relasi ke TahunAkademik (Belongs To)
     */
    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'id_tahun_akademik', 'id_tahun_akademik');
    }
}