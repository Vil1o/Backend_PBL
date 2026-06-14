<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    /**
     * Helper tunggal untuk mengambil profil sekali jalan dan menyimpannya di request atribut
     */
    private function getUserProfile(Request $request)
    {
        // Gunakan caching internal di level request agar tidak nembak API berkali-kali dalam satu hit
        if ($request->attributes->has('user_profile')) {
            return $request->attributes->get('user_profile');
        }

        $token = $request->bearerToken();
        if (!$token) return null;

        try {
            $response = Http::withToken($token)
                ->timeout(5)
                ->get('http://admin4e06.vps-poliban.my.id/api/akademik/users/me');

            if ($response->successful()) {
                $profile = $response->json();
                $request->attributes->set('user_profile', $profile);
                return $profile;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    private function getRole(Request $request)
    {
        $user = $this->getUserProfile($request);
        if (!$user || !isset($user['roles'])) return null;

        $roles = $user['roles'];

        if (in_array('admin_mahasiswa', $roles)) return 'admin_mahasiswa';
        if (in_array('admin_keuangan', $roles)) return 'admin_keuangan';
        if (in_array('pegawai', $roles)) return 'pegawai';
        if (in_array('mahasiswa', $roles)) return 'mahasiswa';

        return null;
    }

    private function getNim(Request $request)
    {
        $user = $this->getUserProfile($request);
        return $user['nomor_identitas'] ?? null;
    }

    /**
     * READ ALL
     */
    public function index(Request $request)
    {
        $role = $this->getRole($request);

        if (!$role) {
            return response()->json(['message' => 'Token tidak valid atau kedaluwarsa.'], 401);
        }

        if (!in_array($role, ['admin_mahasiswa', 'pegawai', 'admin_keuangan'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $mahasiswa = Mahasiswa::select('nim', 'nama_mhs', 'id_prodi')->get();

        return response()->json([
            'status' => 'success',
            'data' => $mahasiswa
        ], 200);
    }

    /**
     * CREATE (Disinkronkan dengan struktur tabel mahasiswa & detail_mahasiswa)
     */
    public function store(Request $request)
    {
        $role = $this->getRole($request);

        if ($role !== 'admin_mahasiswa') {
            return response()->json(['message' => 'Hanya Admin Mahasiswa yang boleh menambah data.'], 403);
        }

        // Validasi sesuai kolom riil di tabel mahasiswa & detail
        $request->validate([
            'nim' => 'required|string|unique:mahasiswa,nim',
            'nama_mhs' => 'required|string|max:100',
            'id_prodi' => 'required|integer', // Diperlukan karena NOT NULL di DB kamu
            'jk' => 'required|in:L,P', // Ditampung untuk detail_mahasiswa
        ]);

        // Gunakan Database Transaction agar jika salah satu gagal, tidak corrupt
        DB::beginTransaction();
        try {
            // 1. Insert ke tabel mahasiswa
            DB::table('mahasiswa')->insert([
                'nim' => $request->nim,
                'nama_mhs' => $request->nama_mhs,
                'id_prodi' => $request->id_prodi,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. Insert ke tabel detail_mahasiswa
            DB::table('detail_mahasiswa')->insert([
                'nim' => $request->nim,
                'jk' => $request->jk,
                'biodata_valid' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data mahasiswa dan detail berhasil ditambahkan.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * READ ONE (Menampilkan data gabungan dengan Detail menggunakan Left Join)
     */
    public function show(Request $request, string $nim)
    {
        $role = $this->getRole($request);
        $userNim = $this->getNim($request);

        if (!$role) {
            return response()->json(['message' => 'Token tidak valid.'], 401);
        }

        if (!in_array($role, ['admin_mahasiswa', 'pegawai', 'admin_keuangan', 'mahasiswa'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($role === 'mahasiswa' && $userNim !== $nim) {
            return response()->json(['message' => 'Anda hanya dapat melihat data sendiri.'], 403);
        }

        // Mengambil data mahasiswa sekaligus detailnya menggunakan Query Builder
        $mahasiswa = DB::table('mahasiswa')
            ->leftJoin('detail_mahasiswa', 'mahasiswa.nim', '=', 'detail_mahasiswa.nim')
            ->where('mahasiswa.nim', $nim)
            ->first();

        if (!$mahasiswa) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $mahasiswa
        ], 200);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, string $nim)
    {
        $role = $this->getRole($request);

        if ($role !== 'admin_mahasiswa') {
            return response()->json(['message' => 'Hanya Admin Mahasiswa yang boleh mengubah data.'], 403);
        }

        $request->validate([
            'nama_mhs' => 'sometimes|required|string|max:100',
            'id_prodi' => 'sometimes|required|integer',
            'jk' => 'sometimes|required|in:L,P',
            'no_telp' => 'sometimes|nullable|string|max:20', // Sesuai kolom database: no_telp
        ]);

        DB::beginTransaction();
        try {
            // Update tabel utama jika ada datanya
            $dataMahasiswa = $request->only(['nama_mhs', 'id_prodi']);
            if (!empty($dataMahasiswa)) {
                DB::table('mahasiswa')->where('nim', $nim)->update($dataMahasiswa);
            }

            // Update tabel detail jika ada datanya
            $dataDetail = $request->only(['jk', 'no_telp']);
            if (!empty($dataDetail)) {
                DB::table('detail_mahasiswa')->where('nim', $nim)->update($dataDetail);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data berhasil diperbarui.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE
     */
    public function destroy(Request $request, string $nim)
    {
        $role = $this->getRole($request);

        if ($role !== 'admin_mahasiswa') {
            return response()->json(['message' => 'Hanya Admin Mahasiswa yang boleh menghapus data.'], 403);
        }

        $deleted = DB::table('mahasiswa')->where('nim', $nim)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        // detail_mahasiswa otomatis terhapus karena ON DELETE CASCADE di level MySQL
        return response()->json([
            'status' => 'success',
            'message' => 'Data mahasiswa berhasil dihapus.'
        ], 200);
    }
}