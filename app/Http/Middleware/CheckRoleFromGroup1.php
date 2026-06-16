<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleFromGroup1
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles): Response
    {
        // 1. Ambil token Bearer dari Header Request
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan. Silahkan login terlebih dahulu.'
            ], 401);
        }

        try {
            // 2. Tembak API Kelompok 1 dengan batas timeout 5 detik
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->get('http://admin4e06.vps-poliban.my.id/api/akademik/users/me'); // Sesuaikan jika port mereka berbeda

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau sudah kedaluwarsa.'
                ], 401);
            }

            $userData = $response->json();
            
            // 3. Menyesuaikan dengan struktur asli Kelompok 1 dari hasil debug
            $userRoles = $userData['roles'] ?? []; // Berupa Array, contoh: ["admin_mahasiswa", "pegawai"]
            $userIdentitas = $userData['nomor_identitas'] ?? null; // Contoh: "AM001"

            // 4. Cek kecocokan menggunakan array_intersect
            // Mencari tahu apakah ada elemen yang sama antara role user dengan role yang diminta route
            $hasAccess = !empty(array_intersect($userRoles, $allowedRoles));

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses (Role tidak sesuai).'
                ], 403);
            }

            // 5. Menyisipkan data ke request agar bisa dibaca di Controller jika dibutuhkan
            $request->merge([
                'auth_user_nim' => $userIdentitas, 
                'auth_user_roles' => $userRoles
            ]);

            return $next($request);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke Authentication Service (Kelompok 1). Waktu tunggu habis.'
            ], 504);
        }
    }
}