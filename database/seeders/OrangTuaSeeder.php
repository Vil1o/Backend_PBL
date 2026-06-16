<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrangTuaSeeder extends Seeder
{
    public function run()
    {
        // 1. Ambil semua NIM mahasiswa yang ada di database lokal
        $nims = DB::table('mahasiswa')->pluck('nim')->toArray();
        
        if (empty($nims)) {
            $this->command->error('Tabel mahasiswa kosong. Silakan jalankan MahasiswaSeeder terlebih dahulu!');
            return;
        }

        $this->command->info('Mulai memproses data Orang Tua (Tanpa library Faker)...');

        // --- DATA DUMMY MANUAL (Pengganti Faker) ---
        $listNamaAyah = ['Budi Santoso', 'Agus Prayitno', 'Hendra Gunawan', 'Iwan Setiawan', 'Bambang Pamungkas', 'Joko Widodo', 'Ahmad Dahlan', 'Rizky Febian', 'Eko Patrio', 'Sule Prikitiew', 'Rahmat Hidayat', 'Zainal Abidin', 'Arif Rahman', 'Supriyanto', 'Teguh Prakoso'];
        $listNamaIbu = ['Siti Aminah', 'Ratna Galih', 'Sri Wahyuni', 'Dewi Lestari', 'Endang Setyawati', 'Nurul Hidayati', 'Rina Nose', 'Ayu Ting Ting', 'Rini Wulandari', 'Sari Nila', 'Lilis Suryani', 'Fitri Carlina', 'Nita Thalia', 'Dian Sastro', 'Maya Septha'];
        
        $listJalan = ['Jl. Mawar', 'Jl. Melati', 'Jl. Anggrek', 'Jl. Kenangan', 'Jl. Pahlawan', 'Jl. Merdeka', 'Jl. Sudirman', 'Jl. Veteran', 'Jl. A. Yani', 'Jl. Hasan Basri'];
        $listKota = ['Banjarmasin', 'Banjarbaru', 'Martapura', 'Pelaihari', 'Kandangan', 'Barabai', 'Amuntai', 'Tanjung'];

        // Setup array untuk randomisasi status
        $statusHidup = ['Hidup', 'Hidup', 'Hidup', 'Meninggal']; 
        $statusKekerabatan = ['Kandung', 'Kandung', 'Kandung', 'Tiri', 'Angkat'];
        $pendidikan = ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'Tidak Sekolah'];
        $pekerjaanAyah = ['PNS', 'Wiraswasta', 'Petani', 'Buruh', 'Karyawan Swasta', 'TNI/Polri', 'Pedagang'];
        $pekerjaanIbu = ['Ibu Rumah Tangga', 'Ibu Rumah Tangga', 'PNS', 'Wiraswasta', 'Karyawan Swasta', 'Pedagang'];
        $penghasilan = [
            'Kurang dari sama dengan 1.000.000',
            '1.000.000-2.000.000',
            '2.000.000-4.000.000',
            '4.000.000-6.000.000',
            'Lebih dari 6.000.000'
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Inisialisasi ID Orang Tua dimulai dari 301
        $idOrangTuaCounter = 301;

        foreach ($nims as $nim) {
            // Ambil nama acak dari array
            $namaAyah = $listNamaAyah[array_rand($listNamaAyah)];
            $namaIbu = $listNamaIbu[array_rand($listNamaIbu)];

            // Membuat email unik berdasarkan nama ayah
            $emailClean = strtolower(str_replace(' ', '', $namaAyah));
            $emailBapak = $emailClean . rand(10, 999) . '@gmail.com';

            // Membuat alamat acak
            $alamat = $listJalan[array_rand($listJalan)] . ' No. ' . rand(1, 150) . ', ' . $listKota[array_rand($listKota)];

            // MENENTUKAN TANGGAL LAHIR ORANG TUA 
            $tglLahirAyah = Carbon::createFromTimestamp(rand(strtotime('1960-01-01'), strtotime('1985-12-31')));
            $tglLahirIbu = Carbon::createFromTimestamp(rand(strtotime('1963-01-01'), strtotime('1988-12-31')));

            // GENERATE NIK AYAH
            $areaAyah = str_pad(rand(110000, 990000), 6, '0', STR_PAD_LEFT);
            $hariAyah = str_pad($tglLahirAyah->day, 2, '0', STR_PAD_LEFT);
            $bulanAyah = str_pad($tglLahirAyah->month, 2, '0', STR_PAD_LEFT);
            $tahunAyah = $tglLahirAyah->format('y'); 
            $suffixAyah = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $nikAyah = $areaAyah . $hariAyah . $bulanAyah . $tahunAyah . $suffixAyah;

            // GENERATE NIK IBU (Perempuan, tanggal + 40)
            $areaIbu = str_pad(rand(110000, 990000), 6, '0', STR_PAD_LEFT);
            $hariIbu = str_pad($tglLahirIbu->day + 40, 2, '0', STR_PAD_LEFT); 
            $bulanIbu = str_pad($tglLahirIbu->month, 2, '0', STR_PAD_LEFT);
            $tahunIbu = $tglLahirIbu->format('y');
            $suffixIbu = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $nikIbu = $areaIbu . $hariIbu . $bulanIbu . $tahunIbu . $suffixIbu;

            // PROSES INSERT/UPDATE KE DATABASE
            DB::table('orang_tua')->updateOrInsert(
                ['nim' => $nim],
                [
                    'id_orang_tua' => $idOrangTuaCounter,
                    
                    // --- DATA AYAH ---
                    'nama_ayah'                => $namaAyah,
                    'nik_ayah'                 => $nikAyah,
                    'tanggal_lahir_ayah'       => $tglLahirAyah->format('Y-m-d'),
                    'status_hidup_ayah'        => $statusHidup[array_rand($statusHidup)],
                    'status_kekerabatan_ayah'  => $statusKekerabatan[array_rand($statusKekerabatan)],
                    'pendidikan_terakhir_ayah' => $pendidikan[array_rand($pendidikan)],
                    'pekerjaan_ayah'           => $pekerjaanAyah[array_rand($pekerjaanAyah)],
                    'penghasilan_ayah'         => $penghasilan[array_rand($penghasilan)],
                    'no_telepon_ayah'          => '08' . rand(100000000, 999999999),
                    
                    // --- DATA IBU ---
                    'nama_ibu'                => $namaIbu,
                    'nik_ibu'                 => $nikIbu,
                    'tanggal_lahir_ibu'       => $tglLahirIbu->format('Y-m-d'),
                    'status_hidup_ibu'        => $statusHidup[array_rand($statusHidup)],
                    'status_kekerabatan_ibu'  => $statusKekerabatan[array_rand($statusKekerabatan)],
                    'pendidikan_terakhir_ibu' => $pendidikan[array_rand($pendidikan)],
                    'pekerjaan_ibu'           => $pekerjaanIbu[array_rand($pekerjaanIbu)],
                    'penghasilan_ibu'         => $penghasilan[array_rand($penghasilan)],
                    'no_telepon_ibu'          => '08' . rand(100000000, 999999999),

                    // --- DATA UMUM ---
                    'alamat' => $alamat,
                    'email'  => $emailBapak,
                ]
            );

            $idOrangTuaCounter++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Seeding Orang Tua berhasil! ID dimulai dari 301 dengan NIK berstandar Kependudukan.');
    }
}