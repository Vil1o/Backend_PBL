<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DetailMahasiswaSeeder extends Seeder
{
    public function run()
    {
        $tokenKelompok1 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWRtaW40ZTA2LnZwcy1wb2xpYmFuLm15LmlkL2FwaS9ha2FkZW1pay9sb2dpbiIsImlhdCI6MTc4MTY1ODkyNCwiZXhwIjoxNzgxNjYyNTI0LCJuYmYiOjE3ODE2NTg5MjQsImp0aSI6IjB4UXFuTVk1ekRkOVlFUU0iLCJzdWIiOiIxIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsInJvbGVfaWRzIjpbMV19.N8hlYhElggHp8yjtx3XTxPbU6r20ummKAOVeZ655d4k';

        $mahasiswaList = DB::table('mahasiswa')->get();
        
        if ($mahasiswaList->isEmpty()) {
            $this->command->error('Tabel mahasiswa kosong. Silakan jalankan MahasiswaSeeder terlebih dahulu!');
            return;
        }

        $this->command->info('Mengambil data Provinsi dan Kelas dari API Kelompok 1...');

        // 1. Fetch Data Provinsi
        $respProv = Http::withToken($tokenKelompok1)->get('http://admin4e06.vps-poliban.my.id/api/akademik/wilayah/provinsi');
        $provinsiList = $respProv->successful() ? ($respProv->json('data') ?? $respProv->json()) : [];

        // 2. Fetch Data Kelas
        $respKelas = Http::withToken($tokenKelompok1)->get('http://admin4e06.vps-poliban.my.id/api/akademik/kelas');
        $kelasMap = [];
        
        if ($respKelas->successful()) {
            $dataKelas = $respKelas->json('data') ?? $respKelas->json();
            foreach ($dataKelas as $kelas) {
                $pId = $kelas['prodi_id'];
                $taId = $kelas['tahun_akademik_id'];
                $kelasMap[$pId][$taId][] = $kelas['id'];
            }
        } else {
            $this->command->warn('Gagal mengambil data Kelas dari API Kelompok 1. id_kelas mungkin akan kosong.');
        }

        $this->command->info('Mulai memproses Detail Mahasiswa...');

        // Cache & List Data Random
        $kabupatenCache = [];
        $statusTinggalList = ['asrama', 'kos', 'rumah orang tua', 'ikut keluarga'];
        $agamaList = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']; 
        $golDarahList = ['A', 'B', 'AB', 'O']; 
        $jkList = ['L', 'P'];
        $transportasiList = ['Kendaraan Umum', 'Sepeda', 'Motor', 'Mobil'];
        
        $idDetailCounter = 101; 

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($mahasiswaList as $mhs) {
            $nim = $mhs->nim;

            $orangTua = DB::table('orang_tua')->where('nim', $nim)->first();
            $asalSekolah = DB::table('asal_sekolah')->where('nim', $nim)->first();

            if (!$orangTua || !$asalSekolah) {
                continue;
            }

            // --- SET API PROVINSI & KOTA SERTA TEMPAT LAHIR ---
            $id_prov = null;
            $id_kota = null;
            $tempat_lahir = 'Banjarmasin'; // Default jika API gagal/kosong

            if (!empty($provinsiList)) {
                $provinsiTerpilih = $provinsiList[array_rand($provinsiList)];
                $id_prov = $provinsiTerpilih['id'];

                if (!isset($kabupatenCache[$id_prov])) {
                    $urlKabupaten = "http://admin4e06.vps-poliban.my.id/api/akademik/wilayah/provinsi/{$id_prov}/kabupaten";
                    $respKab = Http::withToken($tokenKelompok1)->get($urlKabupaten);
                    $kabupatenCache[$id_prov] = $respKab->successful() ? ($respKab->json('data') ?? $respKab->json()) : [];
                }

                if (!empty($kabupatenCache[$id_prov])) {
                    $kabTerpilih = $kabupatenCache[$id_prov][array_rand($kabupatenCache[$id_prov])];
                    $id_kota = $kabTerpilih['id'];
                    
                    // Ekstrak nama kabupaten untuk dijadikan tempat lahir 
                    // (Menggunakan fallback 'nama' atau 'name' karena struktur API wilayah bisa bervariasi)
                    $tempat_lahir = $kabTerpilih['nama'] ?? $kabTerpilih['name'] ?? 'Banjarmasin'; 
                }
            }

            // --- SET API KELAS SESUAI PRODI & TA ---
            $id_kelas = null;
            $mhsProdi = $mhs->id_prodi;
            $mhsTa = $mhs->id_tahun_akademik;

            if (isset($kelasMap[$mhsProdi][$mhsTa])) {
                $pilihanKelas = $kelasMap[$mhsProdi][$mhsTa];
                $id_kelas = $pilihanKelas[array_rand($pilihanKelas)];
            } else if (isset($kelasMap[$mhsProdi])) {
                $semuaTaYgAda = array_keys($kelasMap[$mhsProdi]);
                $taAcak = $semuaTaYgAda[array_rand($semuaTaYgAda)];
                $pilihanKelas = $kelasMap[$mhsProdi][$taAcak];
                $id_kelas = $pilihanKelas[array_rand($pilihanKelas)];
            }

            // --- GENERATE DATA ACAK STRING/ANGKA ---
            $namaClean = strtolower(str_replace([' ', "'", ".", ","], '', $mhs->nama_mhs ?? 'user'));
            $emailPribadi = $namaClean . rand(100, 9999) . '@gmail.com';
            
            $nikFiktif = '6371' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $kkFiktif = '6371' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $rtFiktif = str_pad(rand(1, 20), 2, '0', STR_PAD_LEFT);
            $rwFiktif = str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT);

            DB::table('detail_mahasiswa')->updateOrInsert(
                ['nim' => $nim], 
                [
                    'id_detail'          => $idDetailCounter,
                    'id_orang_tua'       => $orangTua->id_orang_tua,
                    'id_asal_sekolah'    => $asalSekolah->id_asal_sekolah,
                    'id_kelas'           => $id_kelas, 
                    
                    'status_tinggal'     => $statusTinggalList[array_rand($statusTinggalList)],
                    'pekerjaan'          => 'tidak bekerja',
                    'instansi_pekerjaan' => null,
                    'status_nikah'       => 'lajang',
                    
                    'id_prov'            => $id_prov,
                    'id_kota'            => $id_kota,
                    'email_pribadi'      => $emailPribadi,
                    
                    'agama'              => $agamaList[array_rand($agamaList)],
                    'gol_darah'          => $golDarahList[array_rand($golDarahList)],
                    'kewarganegaraan'    => 'WNI',
                    'no_telp'            => '08' . rand(1000000000, 9999999999),

                    'jk'                 => $jkList[array_rand($jkList)],
                    'transportasi'       => $transportasiList[array_rand($transportasiList)],
                    'bb'                 => rand(45, 95), 
                    'tb'                 => rand(150, 185), 
                    'nik'                => $nikFiktif,
                    'no_kk'              => $kkFiktif,
                    'rt'                 => $rtFiktif,
                    'rw'                 => $rwFiktif,
                    
                    // --- PERUBAHAN BARU ---
                    'kebutuhan_khusus'   => 'Tidak',
                    'tempat_lahir'       => $tempat_lahir,
                ]
            );

            $idDetailCounter++; 
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Seeding Detail Mahasiswa (Final: +Tempat Lahir & Kebutuhan Khusus) berhasil diselesaikan!');
    }
}