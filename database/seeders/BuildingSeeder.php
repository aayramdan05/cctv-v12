<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Building;
use App\Models\Faculty;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rawData = <<<EOD
Area Farmasi Baru Gedung Dekanat [ODCA1.2]
Depan Gedung Ppbs (Gedung Biru) [ODCA4.2]
Samping Gedung B Fakultas FISIP [ODCA5.3]
Depan Gedung Dekanat FPIK [ODCB1.2]
Gerbang Pedca Utara [ODCB4.1]
Gerbang Belakang Fakultas FISIP [ODCB5.2]
Belakang Area Ukm Barat [RK11]
Area Pedca Utara Seberang Pos Satpam (G30) [RK16]
Samping Gedung Dekanat Fakultas Farmasi Baru (B4) [RK23]
Samping Lapangan Basket Jurusan Fisika Fakultas FMIPA [RK26]
Area ATM Center [RK30]
Samping Pos 2 Gerbang Utama Dipatukur 35 [RK3DU]
Pos Satpam Pintu Barat (C28) [RK8]
Rektorat Lantai 1 - Resepsionis [WMA1]
Rektorat Lantai 3 - BMN, HULAK [WMA10]
Rektorat Lantai 3 - SINFOR [WMA12]
Rektorat Lantai 3 - Kerjasama [WMA13]
Rektorat Lantai 4 - SPM,SPI,IDB [WMA15]
Rektorat Lantai 4 - Bale Rancage [WMA17]
Rektorat Lantai 1 - Serdos , HUMAS, Rucita [WMA3]
Rektorat Lantai 1 - Bale Sawala [WMA4]
Rektorat Lantai 2 - Akademik [WMA5]
Rektorat Lantai 2 - Kepegawaian [WMA6]
Rektorat Lantai 2 - Rumah Tangga [WMA7]
Rektorat Lantai 2 - WR [WMA9]
FPIK Dekanat [WMB1]
FTG Dekanat [WMB10]
FTG Gedung Perkuliahan 1 [WMB11]
FTG Gedung Perkuliahan 2 [WMB12]
FAPET Gedung Dekanat [WMB13]
FAPET Gedung Perkuliahan 1 [WMB14]
FAPET Gedung Perkuliahan 2 [WMB15]
FAPET Gedung Perkuliahan 3 [WMB16]
FAPET Gedung Perkuliahan 4 [WMB17]
FAPERTA Gedung Dekanat [WMB18]
FAPERTA Gedung Ilmu Tanah [WMB19]
FPIK Gedung Perkuliahan 1 [WMB2]
FAPERTA Gedung Sosek [WMB20]
FAPERTA Gedung HPT [WMB21]
FAPERTA Budidaya [WMB22]
FTIP & FPIK Perkuliahan Bersama [WMB23]
FMIPA Gedung Statistika [WMB24]
FMIPA Gedung Matematika [WMB25]
FAPERTA Multimedia [WMB26]
FMIPA Gedung Dekanat [WMB28]
FTG Gedung Perkuliahan [WMB29]
FPIK Gedung Perkuliahan 2 [WMB3]
FMIPA Gedung Kimia [WMB30]
FMIPA Gedung Biologi [WMB31]
Ex Farmasi Gedung Perkuliahan [WMB32]
Pos Satpam Pintu Utara Gerbang [WMB33]
Farmasi Dekanat [WMB4]
Farmasi Perkuliahan 1 [WMB5]
Farmasi Perkuliahan 2 [WMB6]
FTIP Dekanat [WMB7]
FTIP Gedung Perkuliahan 1 [WMB8]
FTIP Gedung Perkuliahan 2 [WMB9]
FMIPA Gedung Fisika [WMC1]
FK Perkuliahan 2 [WMC10]
FK Perkuliahan 3 [WMC11]
FK Perkuliahan 4 [WMC12]
FKG Gedung Dekanat [WMC14]
FKG Perkuliahan 1 [WMC15]
UKM BARAT [WMC17]
Psikologi Gedung Dekanat [WMC2]
Ekoriparian [WMC23]
ATM Center [WMC24]
Psikologi Gedung Perkuliahan 1 [WMC3]
Psikologi Gedung Perkuliahan 2 [WMC4]
Keperawatan Dekanat [WMC5]
Keperawatan Gedung Perkuliahan 1 [WMC6]
Keperawatan Gedung Perkuliahan 2 [WMC7]
FK Gedung Dekanat [WMC8]
FK Perkuliahan 1 [WMC9]
Stadion Jati [WMD1]
PEDCA SELATAN 2&1 [WMD10]
PEDCA SELATAN 3&8 [WMD11]
PEDCA SELATAN 5&6 [WMD13]
FISIP Gedung Dekanat [WMD15]
FISIP Gedung Perkuliahan 1 & Student Center [WMD16]
FISIP Gedung Perkuliahan 2 [WMD17]
FISIP Gedung Perkuliahan 3 [WMD18]
FIB Gedung Dekanat & TI [WMD19]
UKM TIMUR [WMD2]
FIB Gedung Perkuliahan 1 [WMD20]
FIB Gedung Perkuliahan 2 &SC [WMD21]
FIB Gedung Perkuliahan 3 [WMD22]
FIB PSBJ 1 [WMD23]
FIB PSBJ 2&3 [WMD24]
FIB PSBJ Aula [WMD25]
FH Gedung Perkuliahan 1 [WMD27]
FH Gedung Perkuliahan 2 [WMD28]
Asrama Padjajaran D39 [WMD29]
Gedung Vokasi [WMD3]
Asrama Padjajaran D40 [WMD30]
Asrama PUPR [WMD31]
Bale Kesehatan Jatinangor [WMD33]
Gedung Vokasi Perkuliahan [WMD4]
Bale Padjadjaran 1 [WMD5]
Bale Padjadjaran 2 [WMD6]
Bale Padjadjaran 3 [WMD7]
Bale Padjadjaran 4 [WMD8]
UPT Arsip [WMD9]
FIKOM Gedung Dekanat [WME1]
Ekonomi Perkuliahan 3 [WME10]
Ekonomi Perkuliahan 4 [WME11]
Bale Santika [WME12]
Lapang Basket [WME14]
Pusat Unggulan Inovasi Pelayanan Kefarmasian (PU-IPK) [WME15]
FIKOM Gedung Perkuliahan 1 [WME2]
FIKOM Gedung Perkuliahan 2 [WME3]
FIKOM Gedung Perkuliahan 3 [WME4]
FIKOM Gedung Pasca Sarjana [WME5]
FIKOM Gedung Student Center [WME6]
Ekonomi Gedung Dekanat [WME7]
Ekonomi Perkuliahan 1 [WME8]
Ekonomi Perkuliahan 2 [WME9]
PPBS 1 [WMF1]
Student Center Gedung A [WMF10]
Student Center Gedung B [WMF11]
PPBS 2 [WMF2]
PPBS 3 [WMF3]
PPBS 4 [WMF4]
Perpustakaan Lantai 2 [WMF5]
Perpustakaan Lantai 4 [WMF6]
Laboratorium Lantai 1 [WMF7]
Laboratorium Lantai 3 [WMF8]
Asrama Padjadjaran [WMG10]
Asrama Padjadjaran [WMG11]
RSH [WMG15]
UPHL [WMG16]
KST [WMG17]
Reservoar [WMG19]
Rumah Solar (PLTS) [WMG24]
Gedung 1 (Dipatiukur 35) [WMH1]
Sekolah Pasca Sarjana (Dipatiukur 35) [WMH10]
Graha Sanusi (Dipatiukur 35) [WMH15]
Gedung 2 (Dipatiukur 35) [WMH2]
Bale Kesehatan Singaperbangsa (Singaperbangsa) [WMH32]
Gedung 4 (Dipatiukur 35) [WMH4]
FKG Sekeloa Labkom/Perkuliahan (Sekeloa) [WMH40]
UNPAD Training Center (Dago 4) [WMJ9]
Kampus Cintaratu - Gedung Akademik 1 (Pangandaran) [WML1]
Kampus Cintaratu - Gedung Akademik 2 (Pangandaran) [WML3]
EOD;

        $lines = explode("\n", $rawData);
        $buildingsToInsert = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Extract Name and Code based on format "Name [CODE]" or "Name [ [CODE]"
            if (preg_match('/^(.*?)\s*\[\s*\[?([A-Za-z0-9\.\-]+)\]?\]?$/', $line, $matches)) {
                $namaGedung = trim($matches[1]);
                $kodeGedung = trim($matches[2]);
                
                // Cek agar tidak dimasukin duplikat ke dalam array kita
                if (!isset($buildingsToInsert[$kodeGedung])) {
                    $buildingsToInsert[$kodeGedung] = [
                        'nama_gedung' => $namaGedung,
                        'kode_gedung' => $kodeGedung,
                        'fakultas'    => $this->guessFaculty($namaGedung)
                    ];
                }
            }
        }

        $insertedCount = 0;
        foreach ($buildingsToInsert as $kode => $data) {
            // Validasi: Jangan dimasukin lagi jika kode gedung sudah ada
            $exists = Building::where('kode_gedung', $kode)->exists();
            if (!$exists) {
                Building::create($data);
                
                // Pastikan fakultasnya tercatat di Master Fakultas juga
                Faculty::firstOrCreate(['name' => $data['fakultas']]);
                
                $insertedCount++;
            }
        }

        $this->command->info("Seeder selesai! Berhasil menambahkan $insertedCount gedung baru tanpa duplikasi.");
    }

    private function guessFaculty($name)
    {
        $name = strtoupper($name);
        
        if (str_contains($name, 'FPIK') || str_contains($name, 'PERIKANAN')) return 'Fakultas Perikanan & Ilmu Kelautan';
        if (str_contains($name, 'FTG') || str_contains($name, 'GEOLOGI')) return 'Fakultas Teknik Geologi';
        if (str_contains($name, 'FAPET') || str_contains($name, 'PETERNAKAN')) return 'Fakultas Peternakan';
        if (str_contains($name, 'FAPERTA') || str_contains($name, 'PERTANIAN') || str_contains($name, 'ILMU TANAH')) return 'Fakultas Pertanian';
        if (str_contains($name, 'FMIPA') || str_contains($name, 'STATISTIKA') || str_contains($name, 'MATEMATIKA') || str_contains($name, 'KIMIA') || str_contains($name, 'BIOLOGI') || str_contains($name, 'FISIKA') || str_contains($name, 'PPBS')) return 'Fakultas MIPA';
        if (str_contains($name, 'FARMASI') || str_contains($name, 'PU-IPK')) return 'Fakultas Farmasi';
        if (str_contains($name, 'FTIP') || str_contains($name, 'INDUSTRI PERTANIAN')) return 'Fakultas Teknologi Industri Pertanian';
        if (str_contains($name, 'FKG') || str_contains($name, 'KEDOKTERAN GIGI')) return 'Fakultas Kedokteran Gigi';
        if (str_contains($name, 'FK ') || str_starts_with($name, 'FK') || str_contains($name, 'KEDOKTERAN')) return 'Fakultas Kedokteran';
        if (str_contains($name, 'PSIKOLOGI')) return 'Fakultas Psikologi';
        if (str_contains($name, 'KEPERAWATAN')) return 'Fakultas Keperawatan';
        if (str_contains($name, 'FISIP') || str_contains($name, 'SOSIAL')) return 'Fakultas Ilmu Sosial & Politik';
        if (str_contains($name, 'FIB') || str_contains($name, 'PSBJ') || str_contains($name, 'BUDAYA')) return 'Fakultas Ilmu Budaya';
        if (str_contains($name, 'FH ') || str_starts_with($name, 'FH') || str_contains($name, 'HUKUM')) return 'Fakultas Hukum';
        if (str_contains($name, 'FIKOM') || str_contains($name, 'KOMUNIKASI')) return 'Fakultas Ilmu Komunikasi';
        if (str_contains($name, 'EKONOMI')) return 'Fakultas Ekonomi & Bisnis';
        
        // Kategori Non-Fakultas
        if (str_contains($name, 'REKTORAT') || str_contains($name, 'BALE RANCAGE') || str_contains($name, 'BALE SAWALA')) return 'Rektorat';
        if (str_contains($name, 'PASCA SARJANA') || str_contains($name, 'PASCASARJANA')) return 'Pascasarjana';
        
        return 'Lainnya'; // Default
    }
}
