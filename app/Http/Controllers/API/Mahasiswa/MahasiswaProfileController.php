<?php

namespace App\Http\Controllers\API\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\DetailMahasiswa;
use Illuminate\Http\Request;

class MahasiswaProfileController extends Controller
{
    /**
     * Mengambil data profil lengkap milik mahasiswa yang sedang login
     */
    public function showProfile(Request $request)
    {
        // GANTI BAGIAN INI: Gunakan input dari middleware, BUKAN $request->user()->nim
        $nim = $request->input('auth_user_nim'); 

        $profil = Mahasiswa::with(['detail', 'orangTua', 'asalSekolah', 'tahunAkademik'])->find($nim);

        if (!$profil) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil Anda belum terdaftar di sistem'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $profil
        ], 200);
    }

    /**
     * Mengizinkan mahasiswa melengkapi/mengubah biodata mandiri mereka
     */
    public function updateDetail(Request $request)
    {
        // GANTI BAGIAN INI JUGA
        $nim = $request->input('auth_user_nim');

        $detail = DetailMahasiswa::where('nim', $nim)->first();

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail mahasiswa tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'no_telp' => 'nullable|string|max:15',
            'nik' => 'nullable|string|max:16',
            'no_kk' => 'nullable|string|max:16',
            'alamat' => 'nullable|string',
        ]);

        $detail->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Biodata mandiri Anda berhasil diperbarui',
            'data' => $detail
        ], 200);
    }
}