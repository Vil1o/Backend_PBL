<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use Illuminate\Http\Request;

class MahasiswaFamilyController extends Controller
{
    /**
     * GET: Mengambil data orang tua milik mahasiswa yang sedang login
     */
    public function showFamily(Request $request)
    {
        // Ambil NIM hasil ekstraksi middleware token Kelompok 1
        $nim = $request->input('auth_user_nim');

        $family = OrangTua::where('nim', $nim)->first();

        if (!$family) {
            return response()->json([
                'success' => false,
                'message' => 'Data orang tua belum diisi.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data orang tua berhasil diambil.',
            'data' => $family
        ], 200);
    }

    /**
     * PUT/POST: Menyimpan atau memperbarui data orang tua secara mandiri
     */
    public function storeOrUpdateFamily(Request $request)
    {
        $nim = $request->input('auth_user_nim');

        // Aturan validasi sesuai batasan tipe data baru di phpMyAdmin
        $request->validate([
            // Validasi Ayah
            'nama_ayah'                 => 'nullable|string|max:150',
            'nik_ayah'                  => 'nullable|string|max:20',
            'tanggal_lahir_ayah'        => 'nullable|date',
            'status_hidup_ayah'         => 'nullable|string|max:50',
            'status_kekerabatan_ayah'   => 'nullable|string|max:50',
            'pendidikan_terakhir_ayah'  => 'nullable|string|max:50',
            'pekerjaan_ayah'            => 'nullable|string|max:100',
            'penghasilan_ayah'          => 'nullable|in:Kurang dari sama dengan 1.000.000,1.000.000-2.000.000,2.000.000-4.000.000,4.000.000-6.000.000,Lebih dari 6.000.000',
            'no_telepon_ayah'           => 'nullable|string|max:20',

            // Validasi Ibu
            'nama_ibu'                  => 'nullable|string|max:150',
            'nik_ibu'                   => 'nullable|string|max:20',
            'tanggal_lahir_ibu'         => 'nullable|date',
            'status_hidup_ibu'          => 'nullable|string|max:50',
            'status_kekerabatan_ibu'    => 'nullable|string|max:50',
            'pendidikan_terakhir_ibu'   => 'nullable|string|max:50',
            'pekerjaan_ibu'             => 'nullable|string|max:100',
            'penghasilan_ibu'           => 'nullable|in:Kurang dari sama dengan 1.000.000,1.000.000-2.000.000,2.000.000-4.000.000,4.000.000-6.000.000,Lebih dari 6.000.000',
            'no_telepon_ibu'            => 'nullable|string|max:20',

            // Umum
            'alamat'                    => 'nullable|string',
            'email'                     => 'nullable|email|max:100',
        ]);

        // Cari berdasarkan NIM, kalau belum ada otomatis buat baru (POST), kalau ada langsung update (PUT)
        $family = OrangTua::updateOrCreate(
            ['nim' => $nim],
            $request->all()
        );

        return response()->json([
            'success' => true,
            'message' => 'Data orang tua berhasil disimpan.',
            'data' => $family
        ], 200);
    }
}