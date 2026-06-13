<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa'; // Nama tabel di database
    protected $primaryKey = 'nim';
    
    // Kunci penting: NIM berbentuk string dan bukan auto-incrementing angka
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom tabel mahasiswa yang boleh diisi secara massal
    protected $fillable = [
        'nim', 
        'nama_mhs', 
        'jk',
    ];

    /**
     * Relasi: Mahasiswa terdaftar di sebuah Program Studi
     */

}