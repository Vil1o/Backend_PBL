<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class ExternalMahasiswaController extends Controller
{
    /**
     * Mengambil daftar singkat semua mahasiswa (Hanya NIM dan Nama)
     * Kelompok lain bisa melakukan pencarian lewat query param: ?search=nama_atau_nim
     */
    public function index(Request $request)
    {
        $query = Mahasiswa::select('nim', 'nama_mhs');

        // Fitur pencarian opsional untuk memudahkan kelompok lain
        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where('nim', 'LIKE', "%{$search}%")
                  ->orWhere('nama_mhs', 'LIKE', "%{$search}%");
        }

        $mahasiswa = $query->limit(100)->get(); // Dibatasi 100 data demi performa

        return response()->json([
            'success' => true,
            'message' => 'Daftar singkat mahasiswa berhasil diambil',
            'data' => $mahasiswa
        ], 200);
    }

    /**
     * Mengambil satu data mahasiswa berdasarkan NIM (Untuk validasi kelompok lain)
     */
    public function show($nim)
    {
        $mahasiswa = Mahasiswa::select('nim', 'nama_mhs')->find($nim);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data mahasiswa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data mahasiswa ditemukan',
            'data' => $mahasiswa
        ], 200);
    }
}