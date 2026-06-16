<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TahunAkademikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Sesuaikan dengan endpoint spesifik untuk tahun akademik API Kelompok 1
        $urlAPI = 'http://admin4e06.vps-poliban.my.id/api/akademik/tahun-akademik'; 
        
        // Kosongkan atau isi jika API mereka mewajibkan token Bearer
        $tokenKelompok1 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWRtaW40ZTA2LnZwcy1wb2xpYmFuLm15LmlkL2FwaS9ha2FkZW1pay9sb2dpbiIsImlhdCI6MTc4MTY0NzUyNCwiZXhwIjoxNzgxNjUxMTI0LCJuYmYiOjE3ODE2NDc1MjQsImp0aSI6Ik1ZUVU5UDQyWFBkYVROZ0UiLCJzdWIiOiIxIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsInJvbGVfaWRzIjpbMV19.fd61xWmJ7o2SlkqYOZx1psM2JEXwIEDSSoIDAkWO0yE'; 

        $this->command->info("Mengambil data Tahun Akademik dari API: {$urlAPI} ...");

        $response = Http::withToken($tokenKelompok1)->get($urlAPI);

        if ($response->successful()) {
            
            // Mengambil response JSON
            $tahunAkademikList = $response->json('data') ?? $response->json(); 
            
            if (empty($tahunAkademikList)) {
                $this->command->error('Data tahun akademik kosong atau format tidak sesuai.');
                return;
            }

            $this->command->info('Menyimpan ' . count($tahunAkademikList) . ' data tahun akademik ke database...');

            // Matikan pengecekan Foreign Key sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tahunAkademikList as $ta) {
                
                // MAPPING DATA API KE KOLOM DATABASE
                DB::table('tahun_akademik')->updateOrInsert(
                    ['id_tahun_akademik' => $ta['id']], // Patokan ID dari Kelompok 1
                    [
                        // Langsung masukkan string utuh (misal: "2024 ganjil")
                        'tahun_masuk' => $ta['tahun_akademik'] 
                    ]
                );
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->command->info('Seeding Tahun Akademik berhasil!');
        } else {
            $this->command->error('Gagal mengambil data API. Status Code: ' . $response->status());
            Log::error('API Tahun Akademik Error', ['response' => $response->body()]);
        }
    }
}