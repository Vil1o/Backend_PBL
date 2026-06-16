<?php

namespace App\Http\Controllers\API\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\AsalSekolah;
use Illuminate\Http\Request;

class MahasiswaSchoolController extends Controller
{
    /**
     * Menampilkan data asal sekolah milik mahasiswa yang login
     */
    public function show(Request $request)
    {
        $nim = $request->input('auth_user_nim');

        $sekolah = AsalSekolah::where('nim', $nim)->first();

        if (!$sekolah) {
            return response()->json([
                'success' => false,
                'message' => 'Data asal sekolah belum diisi.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data asal sekolah berhasil diambil',
            'data' => $sekolah
        ], 200);
    }

    /**
     * Menambah atau memperbarui data asal sekolah mandiri
     */
    public function updateOrCreate(Request $request)
    {
        $nim = $request->input('auth_user_nim');

        $request->validate([
            'pendidikan_asal' => 'required|in:SMA,SMK,MA,MAN',
            'nama_sekolah' => 'required|string|max:150',
        ]);

        $data = $request->all();
        $data['nim'] = $nim;

        // Update jika record sudah ada berdasarkan NIM, buat baru jika belum ada
        $sekolah = AsalSekolah::updateOrCreate(
            ['nim' => $nim],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Data asal sekolah berhasil diperbarui',
            'data' => $sekolah
        ], 200);
    }
}