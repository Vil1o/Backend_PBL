<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'detail_mahasiswa';
    protected $primaryKey = 'id_detail';
    
    // Set false jika di tabel phpMyAdmin kamu tidak ada kolom created_at / updated_at
    public $timestamps = false; 

    protected $fillable = [
        'nim',
        'id_kelas',
        'kebutuhan_khusus',
        'jk',
        'tempat_lahir',
        'agama',
        'bb',
        'tb',
        'gol_darah',
        'transportasi',
        'kewarganegaraan',
        'nik',
        'no_kk',
        'status_nikah',
        'pekerjaan',
        'instansi_pekerjaan',
        'no_telp',
        'email_pribadi',
        'rt',
        'rw',
        'status_tinggal',
        'id_prov',
        'id_kota',
        'id_orang_tua',      // Ditambahkan ke fillable
        'id_asal_sekolah'    // Ditambahkan ke fillable
    ];

    /**
     * Relasi Balik ke Data Utama Mahasiswa
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    /**
     * Relasi Baru ke Tabel Orang Tua
     */
    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class, 'id_orang_tua', 'id_orang_tua');
    }

    /**
     * Relasi Baru ke Tabel Asal Sekolah
     */
    public function asalSekolah()
    {
        return $this->belongsTo(AsalSekolah::class, 'id_asal_sekolah', 'id_asal_sekolah');
    }
}