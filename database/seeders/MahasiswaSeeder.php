<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MahasiswaSeeder extends Seeder
{
    public function run()
    {
        // Masukkan token bearer API jika ada
        $tokenKelompok1 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWRtaW40ZTA2LnZwcy1wb2xpYmFuLm15LmlkL2FwaS9ha2FkZW1pay9sb2dpbiIsImlhdCI6MTc4MTY1ODkyNCwiZXhwIjoxNzgxNjYyNTI0LCJuYmYiOjE3ODE2NTg5MjQsImp0aSI6IjB4UXFuTVk1ekRkOVlFUU0iLCJzdWIiOiIxIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsInJvbGVfaWRzIjpbMV19.N8hlYhElggHp8yjtx3XTxPbU6r20ummKAOVeZ655d4k';
        $tokenKelompok4 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWRtaW40ZTA2LnZwcy1wb2xpYmFuLm15LmlkL2FwaS9ha2FkZW1pay9sb2dpbiIsImlhdCI6MTc4MTY1ODkyNCwiZXhwIjoxNzgxNjYyNTI0LCJuYmYiOjE3ODE2NTg5MjQsImp0aSI6IjB4UXFuTVk1ekRkOVlFUU0iLCJzdWIiOiIxIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsInJvbGVfaWRzIjpbMV19.N8hlYhElggHp8yjtx3XTxPbU6r20ummKAOVeZ655d4k';

        $this->command->info("Mengambil data dari API Kelompok 1 dan 4...");

        // 1. Fetch API Kelompok 4 (Kategori UKT)
        $respUkt = Http::withToken($tokenKelompok4)->get('http://keuangan4e06.vps-poliban.my.id/api/kategori-ukt');
        $uktByProdi = [];
        if ($respUkt->successful()) {
            $dataUkt = $respUkt->json('data') ?? [];
            foreach ($dataUkt as $ukt) {
                $uktByProdi[$ukt['id_prodi']][] = $ukt['id_kategori_ukt'];
            }
        }

        // 2. Fetch API Kelompok 4 (Beasiswa)
        $respBeasiswa = Http::withToken($tokenKelompok4)->get('http://keuangan4e06.vps-poliban.my.id/api/beasiswa-master');
        $beasiswaIds = [];
        if ($respBeasiswa->successful()) {
            $dataBeasiswa = $respBeasiswa->json('data') ?? [];
            $beasiswaIds = array_column($dataBeasiswa, 'id_beasiswa');
            array_push($beasiswaIds, null, null, null); // Ada mahasiswa tanpa beasiswa
        }

        // Ambil list ID Tahun Akademik dari database lokalmu
        $dbTahunAkademikIds = DB::table('tahun_akademik')->pluck('id_tahun_akademik')->toArray();

        // 3. Fetch API Kelompok 1 (Mahasiswa)
        $respMhs = Http::withToken($tokenKelompok1)->get('http://admin4e06.vps-poliban.my.id/api/akademik/mahasiswa');

        if ($respMhs->successful()) {
            $mahasiswaList = $respMhs->json('data') ?? $respMhs->json();

            if (empty($mahasiswaList)) {
                $this->command->error('Data mahasiswa dari Kelompok 1 kosong.');
                return;
            }

            $jalanKalimantan = ['Jl. Hasan Basri', 'Jl. Kayu Tangi', 'Jl. A. Yani', 'Jl. Pramuka', 'Jl. Lambung Mangkurat', 'Jl. Pangeran Samudera', 'Jl. Tjilik Riwut', 'Jl. MT Haryono', 'Jl. G. Obos', 'Jl. Veteran'];

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($mahasiswaList as $mhs) {
                // TAMBAHAN: Pastikan email tidak kosong dari API
                if (empty($mhs['nomor_identitas']) || empty($mhs['email'])) continue;

                $id_prodi = $mhs['prodi_id'];

                // MENENTUKAN ID KATEGORI UKT
                $id_kategori = null;
                if (isset($uktByProdi[$id_prodi]) && count($uktByProdi[$id_prodi]) > 0) {
                    $randomKey = array_rand($uktByProdi[$id_prodi]);
                    $id_kategori = $uktByProdi[$id_prodi][$randomKey];
                }

                // MENENTUKAN ID BEASISWA
                $id_beasiswa = null;
                if (!empty($beasiswaIds)) {
                    $id_beasiswa = $beasiswaIds[array_rand($beasiswaIds)];
                }

                // MENENTUKAN TAHUN AKADEMIK 
                $id_tahun_akademik = null;
                if (!empty($dbTahunAkademikIds)) {
                    $id_tahun_akademik = $dbTahunAkademikIds[array_rand($dbTahunAkademikIds)];
                }

                $alamatFiktif = $jalanKalimantan[array_rand($jalanKalimantan)] . ' No. ' . rand(1, 150);

                DB::table('mahasiswa')->updateOrInsert(
                    ['nim' => $mhs['nomor_identitas']],
                    [
                        'nama_mhs'          => $mhs['name'],
                        'email_kampus'      => $mhs['email'], // <-- INI TAMBAHANNYA
                        'id_tahun_akademik' => $id_tahun_akademik, 
                        'id_prodi'          => $id_prodi,
                        'alamat'            => $alamatFiktif, 
                        'id_kategori'       => $id_kategori, 
                        'id_beasiswa'       => $id_beasiswa, 
                    ]
                );
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->command->info('Seeding Mahasiswa (termasuk email_kampus) berhasil diperbarui!');
        } else {
            $this->command->error('Gagal mengambil data API Kelompok 1. Status Code: ' . $respMhs->status());
        }
    }
}