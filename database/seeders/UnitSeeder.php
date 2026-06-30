<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Seed organizational units reflecting Poltekpel Barombong's structure.
     */
    public function run(): void
    {
        // Root: Direktorat
        $direktorat = Unit::create([
            'code' => 'DIR',
            'name' => 'Direktorat',
            'description' => 'Direktorat Politeknik Pelayaran Barombong Makassar',
            'is_active' => true,
        ]);

        // Wakil Direktur
        $wadir1 = Unit::create([
            'code' => 'WADIR-I',
            'name' => 'Wakil Direktur I Bidang Akademik',
            'parent_id' => $direktorat->id,
            'description' => 'Wakil Direktur Bidang Akademik',
            'is_active' => true,
        ]);

        $wadir2 = Unit::create([
            'code' => 'WADIR-II',
            'name' => 'Wakil Direktur II Bidang Keuangan & Umum',
            'parent_id' => $direktorat->id,
            'description' => 'Wakil Direktur Bidang Keuangan, Administrasi Umum dan SDM',
            'is_active' => true,
        ]);

        // Program Studi under Wadir I
        $prodi = [
            ['code' => 'PS-NAUTIKA', 'name' => 'Program Studi Nautika', 'description' => 'Program Studi Diploma Pelayaran Nautika'],
            ['code' => 'PS-TEKNIKA', 'name' => 'Program Studi Teknika', 'description' => 'Program Studi Diploma Pelayaran Teknika'],
            ['code' => 'PS-KALK', 'name' => 'Program Studi KALK', 'description' => 'Program Studi Ketatalaksanaan Angkutan Laut dan Kepelabuhanan'],
            ['code' => 'PS-TLK', 'name' => 'Program Studi TLK', 'description' => 'Program Studi Teknologi Listrik Kapal'],
        ];

        foreach ($prodi as $data) {
            Unit::create([
                ...$data,
                'parent_id' => $wadir1->id,
                'is_active' => true,
            ]);
        }

        // Units under Wadir I - Academic support
        Unit::create([
            'code' => 'UPT-PMI',
            'name' => 'UPT Penjaminan Mutu Internal',
            'parent_id' => $wadir1->id,
            'description' => 'Unit Pelaksana Teknis Penjaminan Mutu Internal',
            'is_active' => true,
        ]);

        Unit::create([
            'code' => 'UPT-PERPUS',
            'name' => 'UPT Perpustakaan',
            'parent_id' => $wadir1->id,
            'description' => 'Unit Pelaksana Teknis Perpustakaan',
            'is_active' => true,
        ]);

        // Bagian under Wadir II
        $bagianKeuangan = Unit::create([
            'code' => 'BAG-KEU',
            'name' => 'Bagian Keuangan',
            'parent_id' => $wadir2->id,
            'description' => 'Bagian Keuangan dan Perencanaan',
            'is_active' => true,
        ]);

        Unit::create([
            'code' => 'BAG-UMUM',
            'name' => 'Bagian Umum & SDM',
            'parent_id' => $wadir2->id,
            'description' => 'Bagian Administrasi Umum dan Sumber Daya Manusia',
            'is_active' => true,
        ]);

        Unit::create([
            'code' => 'BAG-RENBANG',
            'name' => 'Bagian Perencanaan & Pengembangan',
            'parent_id' => $wadir2->id,
            'description' => 'Bagian Perencanaan Program dan Pengembangan',
            'is_active' => true,
        ]);

        // Sub-bagian under Keuangan
        Unit::create([
            'code' => 'SUB-ANGG',
            'name' => 'Sub Bagian Anggaran',
            'parent_id' => $bagianKeuangan->id,
            'description' => 'Sub Bagian Perencanaan dan Pengelolaan Anggaran',
            'is_active' => true,
        ]);

        Unit::create([
            'code' => 'SUB-PERBEND',
            'name' => 'Sub Bagian Perbendaharaan',
            'parent_id' => $bagianKeuangan->id,
            'description' => 'Sub Bagian Perbendaharaan dan Pencairan Dana',
            'is_active' => true,
        ]);

        // Diklat unit
        Unit::create([
            'code' => 'DIKLAT',
            'name' => 'Unit Diklat',
            'parent_id' => $wadir1->id,
            'description' => 'Unit Pendidikan dan Pelatihan',
            'is_active' => true,
        ]);
    }
}
