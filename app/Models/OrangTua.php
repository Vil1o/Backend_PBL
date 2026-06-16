<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    use HasFactory;

    protected $table = 'orang_tua';
    protected $primaryKey = 'id_orang_tua';
    public $timestamps = false; // Set true jika tabelmu punya created_at & updated_at

    protected $fillable = [
        'nim',
        'nama_ayah',
        'nik_ayah',
        'tanggal_lahir_ayah',
        'status_hidup_ayah',
        'status_kekerabatan_ayah',
        'pendidikan_terakhir_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'no_telepon_ayah',
        'nama_ibu',
        'nik_ibu',
        'tanggal_lahir_ibu',
        'status_hidup_ibu',
        'status_kekerabatan_ibu',
        'pendidikan_terakhir_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'no_telepon_ibu',
        'alamat',
        'email'
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function detailMahasiswa()
    {
        return $this->hasOne(DetailMahasiswa::class, 'id_orang_tua', 'id_orang_tua');
    }
}