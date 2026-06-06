<?php

namespace Database\Seeders;

use App\Models\Warga;
use Illuminate\Database\Seeder;

class WargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generate 100 dummy warga records for testing.
     */
    public function run(): void
    {
        $kelurahans = ['SUKAMAJU', 'CILANDAK', 'KEBAYORAN', 'MENTENG', 'TEBET'];
        $kecamatans = ['PASAR MINGGU', 'CILANDAK', 'KEBAYORAN BARU', 'MENTENG', 'TEBET'];
        $namaDepan = ['BUDI', 'SITI', 'AHMAD', 'DEWI', 'RUDI', 'ANITA', 'JOKO', 'SRI', 'AGUS', 'RINA'];
        $namaBelakang = ['SANTOSO', 'WIJAYA', 'PRATAMA', 'SUSANTI', 'HIDAYAT', 'LESTARI', 'SAPUTRA', 'RAHAYU', 'KURNIAWAN', 'WULANDARI'];
        $tempatLahir = ['JAKARTA', 'BANDUNG', 'SURABAYA', 'SEMARANG', 'YOGYAKARTA', 'BOGOR', 'DEPOK', 'TANGERANG', 'BEKASI', 'MEDAN'];

        for ($i = 0; $i < 100; $i++) {
            $gender = rand(0, 1) ? 'LAKI-LAKI' : 'PEREMPUAN';
            $kelIdx = array_rand($kelurahans);

            Warga::create([
                'nik' => str_pad((string) rand(3200000000000000, 3299999999999999), 16, '0', STR_PAD_LEFT),
                'nama' => $namaDepan[array_rand($namaDepan)] . ' ' . $namaBelakang[array_rand($namaBelakang)],
                'tempat_lahir' => $tempatLahir[array_rand($tempatLahir)],
                'tanggal_lahir' => now()->subYears(rand(20, 70))->subDays(rand(0, 365))->format('Y-m-d'),
                'jenis_kelamin' => $gender,
                'alamat' => 'JL. ' . ['MERDEKA', 'SUDIRMAN', 'GATOT SUBROTO', 'DIPONEGORO', 'AHMAD YANI'][array_rand(['MERDEKA', 'SUDIRMAN', 'GATOT SUBROTO', 'DIPONEGORO', 'AHMAD YANI'])] . ' NO. ' . rand(1, 200),
                'rt' => str_pad((string) rand(1, 20), 3, '0', STR_PAD_LEFT),
                'rw' => str_pad((string) rand(1, 10), 3, '0', STR_PAD_LEFT),
                'kelurahan_desa' => $kelurahans[$kelIdx],
                'kecamatan' => $kecamatans[$kelIdx],
                'status_ambil' => false,
            ]);
        }
    }
}
