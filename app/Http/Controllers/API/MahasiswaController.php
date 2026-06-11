<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Import untuk tembak API VPS Login

class MahasiswaController extends Controller
{
    /**
     * Mengambil data profil user dari API kelompok login menggunakan Bearer Token
     */
   private function getUserProfileFromGroup(Request $request)
{
    $token = $request->bearerToken();

    if (!$token) {
        return null;
    }

    try {
        $response = Http::withToken($token)
            ->get('http://admin4e06.vps-poliban.my.id/api/akademik/users/me');

        if ($response->successful()) {
            return $response->json();
        }
    } catch (\Exception $e) {
        return null;
    }

    return null;
}

   private function getRole(Request $request)
{
    $user = $this->getUserProfileFromGroup($request);

    if (!$user || !isset($user['roles'])) {
        return null;
    }

    $roles = $user['roles'];

    // Prioritas tertinggi
    if (in_array('admin_mahasiswa', $roles)) {
        return 'admin_mahasiswa';
    }

    if (in_array('admin_keuangan', $roles)) {
        return 'admin_keuangan';
    }

    if (in_array('pegawai', $roles)) {
        return 'pegawai';
    }

    if (in_array('mahasiswa', $roles)) {
        return 'mahasiswa';
    }

    return null;
}

private function getNim(Request $request)
{
    $user = $this->getUserProfileFromGroup($request);

    if (!$user) {
        return null;
    }

    return $user['nomor_identitas'] ?? null;
}

    /**
     * READ ALL: Diubah agar Admin Mahasiswa dan Pegawai bisa melihat semua data mahasiswa
     */
    public function index(Request $request)
{
    $role = $this->getRole($request);

    if (!$role) {
        return response()->json([
            'message' => 'Token tidak valid atau kedaluwarsa.'
        ], 401);
    }

   if (!in_array($role, [
    'admin_mahasiswa',
    'pegawai',
    'admin_keuangan'
    ])) {
        return response()->json([
            'message' => 'Akses ditolak.'
        ], 403);
    }

    $mahasiswa = Mahasiswa::select('nim', 'nama_lengkap')->get();

    return response()->json([
        'status' => 'success',
        'data' => $mahasiswa
    ], 200);
}

    /**
     * CREATE: Tetap khusus 'admin' saja yang boleh menambah mahasiswa baru.
     */
   public function store(Request $request)
{
    $role = $this->getRole($request);

    if (!$role) {
        return response()->json([
            'message' => 'Token tidak valid.'
        ], 401);
    }

    if ($role !== 'admin_mahasiswa') {
        return response()->json([
            'message' => 'Hanya Admin Mahasiswa yang boleh menambah data.'
        ], 403);
    }

    $validatedData = $request->validate([
        'nim' => 'required|string|unique:mahasiswa,nim',
        'nama_lengkap' => 'required|string|max:100',
        'jk' => 'required|in:L,P',
    ]);

    $mahasiswa = Mahasiswa::create($validatedData);

    return response()->json([
        'status' => 'success',
        'message' => 'Data mahasiswa berhasil ditambahkan.',
        'data' => $mahasiswa
    ], 201);
}

    /**
     * READ ONE: Selain Admin/Dosen/Pegawai/Keuangan, Mahasiswa hanya boleh melihat datanya sendiri.
     */
public function show(Request $request, string $nim)
{
    $role = $this->getRole($request);
    $userNim = $this->getNim($request);

    if (!$role) {
        return response()->json([
            'message' => 'Token tidak valid.'
        ], 401);
    }

    $allowedRoles = [
        'admin_mahasiswa',
        'pegawai',
        'admin_keuangan',
        'mahasiswa'
    ];

    if (!in_array($role, $allowedRoles)) {
        return response()->json([
            'message' => 'Akses ditolak.'
        ], 403);
    }

    if ($role === 'mahasiswa' && $userNim !== $nim) {
        return response()->json([
            'message' => 'Anda hanya dapat melihat data sendiri.'
        ], 403);
    }

    $mahasiswa = Mahasiswa::find($nim);

    if (!$mahasiswa) {
        return response()->json([
            'message' => 'Data tidak ditemukan.'
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => $mahasiswa
    ], 200);
}

    /**
     * UPDATE: Hanya Admin atau Mahasiswa bersangkutan yang boleh edit data.
     */
    public function update(Request $request, string $nim)
{
    $role = $this->getRole($request);

    if (!$role) {
        return response()->json([
            'message' => 'Token tidak valid.'
        ], 401);
    }

    if ($role !== 'admin_mahasiswa') {
        return response()->json([
            'message' => 'Hanya Admin Mahasiswa yang boleh mengubah data.'
        ], 403);
    }

    $mahasiswa = Mahasiswa::find($nim);

    if (!$mahasiswa) {
        return response()->json([
            'message' => 'Data tidak ditemukan.'
        ], 404);
    }

    $validatedData = $request->validate([
        'nama_lengkap' => 'sometimes|required|string|max:100',
        'jk' => 'sometimes|required|in:L,P',
        'no_hp' => 'sometimes|nullable|string|max:20',
    ]);

    $mahasiswa->update($validatedData);

    return response()->json([
        'status' => 'success',
        'message' => 'Data berhasil diperbarui.',
        'data' => $mahasiswa
    ], 200);
}

    /**
     * DELETE: Tetap dikunci khusus untuk 'admin' saja.
     */
 public function destroy(Request $request, string $nim)
{
    $role = $this->getRole($request);

    if (!$role) {
        return response()->json([
            'message' => 'Token tidak valid.'
        ], 401);
    }

    if ($role !== 'admin_mahasiswa') {
        return response()->json([
            'message' => 'Hanya Admin Mahasiswa yang boleh menghapus data.'
        ], 403);
    }

    $mahasiswa = Mahasiswa::find($nim);

    if (!$mahasiswa) {
        return response()->json([
            'message' => 'Data tidak ditemukan.'
        ], 404);
    }

    $mahasiswa->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Data mahasiswa berhasil dihapus.'
    ], 200);
}
}