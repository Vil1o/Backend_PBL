<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AsalSekolahSeeder extends Seeder
{
    public function run()
    {
        // Masukkan token bearer API jika ada
        $tokenKelompok1 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWRtaW40ZTA2LnZwcy1wb2xpYmFuLm15LmlkL2FwaS9ha2FkZW1pay9sb2dpbiIsImlhdCI6MTc4MTY0NzUyNCwiZXhwIjoxNzgxNjUxMTI0LCJuYmYiOjE3ODE2NDc1MjQsImp0aSI6Ik1ZUVU5UDQyWFBkYVROZ0UiLCJzdWIiOiIxIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsInJvbGVfaWRzIjpbMV19.fd61xWmJ7o2SlkqYOZx1psM2JEXwIEDSSoIDAkWO0yE';

        // 1. Ambil semua NIM mahasiswa yang ada di database lokalmu
        $nims = DB::table('mahasiswa')->pluck('nim')->toArray();
        
        if (empty($nims)) {
            $this->command->error('Tabel mahasiswa kosong. Silakan jalankan MahasiswaSeeder terlebih dahulu!');
            return;
        }

        $this->command->info('Mengambil data Provinsi dari API Kelompok 1...');

        $respProv = Http::withToken($tokenKelompok1)->get('http://admin4e06.vps-poliban.my.id/api/akademik/wilayah/provinsi');
        
        if (!$respProv->successful()) {
            $this->command->error('Gagal mengambil data Provinsi dari API.');
            return;
        }

        $provinsiList = $respProv->json('data') ?? $respProv->json();
        
        if (empty($provinsiList)) {
            $this->command->error('Data Provinsi kosong.');
            return;
        }

        $kabupatenCache = []; 
        
        // HANYA SMA, SMK, dan MA (Sesuai dengan perubahan tabel kamu)
        $jenisSekolahList = ['SMA', 'SMK', 'MA'];
        
        $jalanList = ['Jl. Pendidikan', 'Jl. Pahlawan', 'Jl. Merdeka', 'Jl. Sudirman', 'Jl. Kartini', 'Jl. Ki Hajar Dewantara', 'Jl. Pelajar'];
        
        $this->command->info('Mulai memproses data Asal Sekolah...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($nims as $nim) {
            $provinsiTerpilih = $provinsiList[array_rand($provinsiList)];
            $id_prov = $provinsiTerpilih['id'];

            // Fetch API Kabupaten (Pakai Cache agar cepat)
            if (!isset($kabupatenCache[$id_prov])) {
                $urlKabupaten = "http://admin4e06.vps-poliban.my.id/api/akademik/wilayah/provinsi/{$id_prov}/kabupaten";
                $respKab = Http::withToken($tokenKelompok1)->get($urlKabupaten);
                
                $kabupatenCache[$id_prov] = $respKab->successful() ? ($respKab->json('data') ?? $respKab->json()) : [];
            }

            $kabupatenList = $kabupatenCache[$id_prov];
            
            // Jika ada provinsi yang ternyata belum ada kabupatennya di API Kel 1, kita skip mahasiswanya 
            // agar nama sekolah tidak perlu mengambil dari nama provinsi
            if (empty($kabupatenList)) {
                continue; 
            }

            // Pilih 1 kabupaten secara acak
            $kabupatenTerpilih = $kabupatenList[array_rand($kabupatenList)];
            $id_kota = $kabupatenTerpilih['id'];
            
            // Membersihkan kata "Kabupaten" / "Kota" agar nama sekolah bagus
            $namaKotaBersih = str_replace(['Kabupaten ', 'Kota '], '', $kabupatenTerpilih['nama']);

            // MENENTUKAN PENDIDIKAN ASAL & NAMA SEKOLAH (Fokus Kabupaten)
            $pendidikan_asal = $jenisSekolahList[array_rand($jenisSekolahList)];
            $nama_sekolah = $pendidikan_asal . 'N ' . rand(1, 5) . ' ' . $namaKotaBersih; 

            // MENENTUKAN ALAMAT & NOMOR TELEPON 
            $alamat_sekolah = $jalanList[array_rand($jalanList)] . ' No. ' . rand(1, 200);
            $no_telepon = '08' . rand(100000000, 999999999);

            // PROSES INSERT/UPDATE KE DATABASE
            DB::table('asal_sekolah')->updateOrInsert(
                ['nim' => $nim], 
                [
                    'id_asal_sekolah' => rand(1, 99999), 
                    'pendidikan_asal' => $pendidikan_asal,
                    'id_prov'         => $id_prov,
                    'id_kota'         => $id_kota,
                    'nama_sekolah'    => $nama_sekolah, // Fix murni menggunakan Kabupaten/Kota
                    'alamat_sekolah'  => $alamat_sekolah,
                    'no_telepon'      => $no_telepon,
                ]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Seeding Asal Sekolah berhasil diperbarui!');
    }
}