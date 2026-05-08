<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Building;
use App\Models\User;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Daftar Fakultas Default (dari sistem lama)
        $defaultFaculties = [
            'Fakultas Hukum',
            'Fakultas Ekonomi & Bisnis',
            'Fakultas Kedokteran',
            'Fakultas MIPA',
            'Fakultas Pertanian',
            'Fakultas Kedokteran Gigi',
            'Fakultas Ilmu Sosial & Politik',
            'Fakultas Ilmu Budaya',
            'Fakultas Psikologi',
            'Fakultas Peternakan',
            'Fakultas Ilmu Komunikasi',
            'Fakultas Keperawatan',
            'Fakultas Perikanan & Ilmu Kelautan',
            'Fakultas Teknologi Industri Pertanian',
            'Fakultas Farmasi',
            'Fakultas Teknik Geologi',
            'Pascasarjana',
            'Rektorat',
        ];

        // 2. Ambil juga nama-nama fakultas yang mungkin sudah diketik custom di tabel buildings
        $existingBuildingFaculties = Building::distinct()->pluck('fakultas')->filter()->toArray();
        
        // 3. Ambil nama-nama fakultas dari tabel users
        $existingUserFaculties = User::distinct()->pluck('faculty')->filter()->toArray();

        // Gabungkan semua nama, hilangkan duplikat
        $allFaculties = array_unique(array_merge($defaultFaculties, $existingBuildingFaculties, $existingUserFaculties));

        // Insert ke database
        foreach ($allFaculties as $facultyName) {
            Faculty::firstOrCreate(['name' => trim($facultyName)]);
        }

        $this->command->info('Data Fakultas berhasil di-sync dan di-seed!');
    }
}
