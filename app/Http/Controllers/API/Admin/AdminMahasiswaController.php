<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\DetailMahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMahasiswaController extends Controller
{
    /**
     * Menampilkan list mahasiswa untuk dashboard admin (dengan pagination)
     */
    public function index()
    {
        $mahasiswa = Mahasiswa::with('tahunAkademik')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Daftar data mahasiswa berhasil diambil',
            'data' => $mahasiswa->items(), // Hanya mengambil daftar datanya saja
            'pagination' => [
                'current_page' => $mahasiswa->currentPage(),
                'last_page'    => $mahasiswa->lastPage(),
                'per_page'     => $mahasiswa->perPage(),
                'total'        => $mahasiswa->total(),
                'next_page_url'=> $mahasiswa->nextPageUrl(),
                'prev_page_url'=> $mahasiswa->previousPageUrl(),
            ]
        ], 200);
    }

    /**
     * Menyimpan data mahasiswa baru sekaligus membuat baris kosong di detail_mahasiswa
     */
    public function store(Request $request)
    {
        $request->validate([
            'nim' => 'required|string|unique:mahasiswa,nim|max:20',
            'nama_mhs' => 'required|string|max:255',
            'id_tahun_akademik' => 'required|integer',
            'id_prodi' => 'required|integer',
        ]);

        // Menggunakan Database Transaction agar jika salah satu gagal, semua dibatalkan
        DB::beginTransaction();

        try {
            $mahasiswa = Mahasiswa::create($request->all());

            // Otomatis buatkan data detail kosong/default terikat dengan NIM baru
            DetailMahasiswa::create([
                'nim' => $mahasiswa->nim,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mahasiswa berhasil ditambahkan',
                'data' => $mahasiswa
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan profil super lengkap mahasiswa (Join semua tabel relasi)
     */
    public function show($nim)
    {
        $mahasiswa = Mahasiswa::with(['detail', 'orangTua', 'asalSekolah', 'tahunAkademik'])->find($nim);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $mahasiswa
        ], 200);
    }

    /**
     * Mengubah data utama mahasiswa
     */
    public function update(Request $request, $nim)
    {
        $mahasiswa = Mahasiswa::find($nim);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ], 404);
        }

        $mahasiswa->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data mahasiswa berhasil diperbarui',
            'data' => $mahasiswa
        ], 200);
    }

    /**
     * Menghapus mahasiswa (Otomatis menghapus detail, ortu, sekolah karena CASCADE)
     */
    public function destroy($nim)
    {
        $mahasiswa = Mahasiswa::find($nim);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ], 404);
        }

        $mahasiswa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data mahasiswa dan relasinya berhasil dihapus'
        ], 200);
    }
}