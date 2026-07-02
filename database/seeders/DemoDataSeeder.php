<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityBudget;
use App\Models\ActivityIndicator;
use App\Models\BudgetRealization;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\ProgramIndicator;
use App\Models\Renja;
use App\Models\Renstra;
use App\Models\RenstraIndicator;
use App\Models\Role;
use App\Models\SubActivity;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get or Create Admin & Users
        $superAdminRole = Role::where('name', 'super-admin')->first();
        $direkturRole = Role::where('name', 'direktur')->first();
        $wadirRole = Role::where('name', 'wakil-direktur')->first();
        $kabagRole = Role::where('name', 'kepala-bagian')->first();
        $stafPerencanaanRole = Role::where('name', 'staf-perencanaan')->first();
        $operatorRole = Role::where('name', 'operator-unit')->first();

        $directorate = Unit::where('code', 'DIR')->first();
        $wadir1 = Unit::where('code', 'WADIR-I')->first();
        $bagKeuangan = Unit::where('code', 'BAG-KEU')->first();
        $diklatUnit = Unit::where('code', 'DIKLAT')->first();

        // Create Admin
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@nautiplan.test',
            'employee_id' => '198805122010121001',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'unit_id' => $directorate->id,
            'is_active' => true,
        ]);
        $admin->roles()->attach($superAdminRole->id);

        // Create Direktur
        $direkturUser = User::create([
            'name' => 'Capt. Stefani, M.Mar.',
            'email' => 'direktur@nautiplan.test',
            'employee_id' => '197508242001121002',
            'phone' => '081122334455',
            'password' => Hash::make('password'),
            'unit_id' => $directorate->id,
            'is_active' => true,
        ]);
        $direkturUser->roles()->attach($direkturRole->id);
        $directorate->update(['head_user_id' => $direkturUser->id]);

        // Create Wadir I
        $wadirUser = User::create([
            'name' => 'Dr. Ir. Herry, M.T.',
            'email' => 'wadir1@nautiplan.test',
            'employee_id' => '197803152003121003',
            'phone' => '081223344556',
            'password' => Hash::make('password'),
            'unit_id' => $wadir1->id,
            'is_active' => true,
        ]);
        $wadirUser->roles()->attach($wadirRole->id);
        $wadir1->update(['head_user_id' => $wadirUser->id]);

        // Create Perencanaan
        $plannerUser = User::create([
            'name' => 'Andi Perencana, S.E.',
            'email' => 'planner@nautiplan.test',
            'employee_id' => '198504102008121004',
            'phone' => '081334455667',
            'password' => Hash::make('password'),
            'unit_id' => $bagKeuangan->id,
            'is_active' => true,
        ]);
        $plannerUser->roles()->attach($stafPerencanaanRole->id);

        // Create Operator Unit Diklat
        $operatorUser = User::create([
            'name' => 'Budi Operator Diklat',
            'email' => 'operator@nautiplan.test',
            'employee_id' => '199011302015031005',
            'phone' => '081445566778',
            'password' => Hash::make('password'),
            'unit_id' => $diklatUnit->id,
            'is_active' => true,
        ]);
        $operatorUser->roles()->attach($operatorRole->id);

        // Get active Fiscal Year (2026)
        $fy2026 = FiscalYear::where('year', 2026)->first();

        // 2. Seed Renstra (Strategic Plan 2025-2029)
        $renstra = Renstra::create([
            'title' => 'Rencana Strategis Poltekpel Barombong 2025-2029',
            'description' => 'Peningkatan Mutu Pendidikan Vokasi Pelayaran yang Unggul, Berstandar Internasional, dan Berkarakter.',
            'start_year' => 2025,
            'end_year' => 2029,
            'status' => 'active',
            'vision' => 'Menjadi Politeknik Pelayaran berstandar internasional yang menghasilkan sumber daya manusia perhubungan laut yang unggul, prima, dan beretika.',
            'mission' => [
                'Menyelenggarakan pendidikan dan pelatihan vokasi pelayaran berstandar internasional.',
                'Melaksanakan penelitian ilmiah dan pengabdian masyarakat di bidang teknologi pelayaran.',
                'Mewujudkan tata kelola organisasi yang transparan, akuntabel, dan berbasis teknologi informasi.',
                'Membangun jejaring kerja sama dengan industri pelayaran nasional dan global.',
            ],
            'created_by' => $admin->id,
        ]);

        // Renstra Indicators
        $renstraIndicators = [
            [
                'code' => 'IND-REN-01',
                'name' => 'Persentase kelulusan taruna tepat waktu',
                'target_value' => 95.0,
                'unit_of_measure' => 'persen',
                'baseline_value' => 88.0,
            ],
            [
                'code' => 'IND-REN-02',
                'name' => 'Indeks kepuasan pengguna lulusan (skala 1-4)',
                'target_value' => 3.5,
                'unit_of_measure' => 'skala',
                'baseline_value' => 3.1,
            ],
            [
                'code' => 'IND-REN-03',
                'name' => 'Jumlah publikasi ilmiah internasional terindeks',
                'target_value' => 15,
                'unit_of_measure' => 'jurnal',
                'baseline_value' => 4,
            ],
        ];

        foreach ($renstraIndicators as $ind) {
            RenstraIndicator::create([
                'renstra_id' => $renstra->id,
                ...$ind,
            ]);
        }

        // 3. Seed Renja (Annual Work Plan 2026) for Diklat
        $renja = Renja::create([
            'title' => 'Rencana Kerja Tahunan Unit Diklat TA 2026',
            'fiscal_year_id' => $fy2026->id,
            'renstra_id' => $renstra->id,
            'unit_id' => $diklatUnit->id,
            'status' => 'approved',
            'total_budget' => 12500000000.0, // 12.5 Milyar
            'created_by' => $plannerUser->id,
        ]);

        // 4. Seed Programs
        // Program 1: Pendidikan Vokasi Pelayaran (DL)
        $progAkademik = Program::create([
            'code' => 'DL.1975',
            'name' => 'Program Pendidikan dan Pelatihan Vokasi Pelayaran',
            'description' => 'Program penyelenggaraan pendidikan vokasi diploma dan diklat keahlian/keterampilan pelayaran.',
            'renstra_id' => $renstra->id,
            'unit_id' => $diklatUnit->id,
            'fiscal_year_id' => $fy2026->id,
            'objective' => 'Meningkatkan kompetensi taruna dan peserta diklat berstandar IMO (International Maritime Organization).',
            'status' => 'active',
            'priority' => 'critical',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'total_budget' => 95000000000.0, // 95 Milyar
            'created_by' => $admin->id,
        ]);

        ProgramIndicator::create([
            'program_id' => $progAkademik->id,
            'code' => 'IND-PROG-DL01',
            'name' => 'Persentase peserta diklat lulus sertifikasi kompetensi keahlian laut',
            'target_value' => 90.0,
            'actual_value' => 88.5,
            'unit_of_measure' => 'persen',
        ]);

        // Program 2: Dukungan Manajemen (WA)
        $progUmum = Program::create([
            'code' => 'WA.3996',
            'name' => 'Program Dukungan Manajemen BPSDM',
            'description' => 'Penyediaan layanan umum, administrasi perkantoran, dan fasilitas operasional kampus.',
            'renstra_id' => $renstra->id,
            'unit_id' => $bagKeuangan->id,
            'fiscal_year_id' => $fy2026->id,
            'objective' => 'Meningkatkan tata kelola administrasi keuangan dan sarana prasarana yang transparan dan akuntabel.',
            'status' => 'active',
            'priority' => 'high',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'total_budget' => 25000000000.0, // 25 Milyar
            'created_by' => $admin->id,
        ]);

        ProgramIndicator::create([
            'program_id' => $progUmum->id,
            'code' => 'IND-PROG-WA01',
            'name' => 'Persentase realisasi anggaran dan kepatuhan administratif',
            'target_value' => 98.0,
            'actual_value' => 96.7,
            'unit_of_measure' => 'persen',
        ]);

        // 5. Seed Activities
        // Activity 1.1: Diklat Peningkatan Keahlian Pelaut (under DL.1975)
        $actDiklat = Activity::create([
            'code' => 'DL.1975.DCB.004',
            'name' => 'Diklat Peningkatan Kompetensi Penjenjangan Transportasi Laut',
            'description' => 'Pelaksanaan program diklat peningkatan kompetensi laut untuk Nautika & Teknika Tingkat II, III, IV, V.',
            'program_id' => $progAkademik->id,
            'renja_id' => $renja->id,
            'unit_id' => $diklatUnit->id,
            'fiscal_year_id' => $fy2026->id,
            'responsible_user_id' => $operatorUser->id,
            'status' => 'approved',
            'priority' => 'critical',
            'start_date' => '2026-02-01',
            'end_date' => '2026-11-30',
            'progress_percentage' => 45,
            'location' => 'Kampus II Poltekpel Barombong',
        ]);

        // Activity 1.2: Pemeliharaan Sarana Simulator & Laboratorium (under WA.3996)
        $actPemeliharaan = Activity::create([
            'code' => 'WA.3996.BMA.005',
            'name' => 'Pemeliharaan Sarana Laboratorium & Simulator Pelayaran',
            'description' => 'Kalibrasi, perbaikan, dan upgrade perangkat lunak/keras simulator Bridge and Engine Room.',
            'program_id' => $progUmum->id,
            'renja_id' => $renja->id,
            'unit_id' => $bagKeuangan->id,
            'fiscal_year_id' => $fy2026->id,
            'responsible_user_id' => $plannerUser->id,
            'status' => 'in_progress',
            'priority' => 'high',
            'start_date' => '2026-03-01',
            'end_date' => '2026-10-31',
            'progress_percentage' => 30,
            'location' => 'Gedung Simulator Utama',
        ]);

        // 6. Seed Sub Activities
        // Sub Activities for Diklat
        SubActivity::create([
            'activity_id' => $actDiklat->id,
            'name' => 'Registrasi dan Seleksi Administrasi Calon Peserta',
            'description' => 'Proses seleksi dokumen pelaut untuk persyaratan kelas penjenjangan.',
            'status' => 'completed',
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-15',
            'progress_percentage' => 100,
            'assigned_to' => $operatorUser->id,
        ]);

        SubActivity::create([
            'activity_id' => $actDiklat->id,
            'name' => 'Pelaksanaan Pembelajaran Teori & Praktek Simulator',
            'description' => 'Kuliah umum, praktikum simulator, dan simulasi keselamatan kapal.',
            'status' => 'in_progress',
            'start_date' => '2026-03-01',
            'end_date' => '2026-09-30',
            'progress_percentage' => 60,
            'assigned_to' => $operatorUser->id,
        ]);

        SubActivity::create([
            'activity_id' => $actDiklat->id,
            'name' => 'Ujian Keahlian Pelaut (UKP)',
            'description' => 'Penyelenggaraan ujian kompetensi negara bekerjasama dengan Dewan Penguji Keahlian Pelaut.',
            'status' => 'pending',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-25',
            'progress_percentage' => 0,
            'assigned_to' => $operatorUser->id,
        ]);

        // 7. Seed Budgets (ActivityBudgets)
        // Budgets for Diklat Activity
        $bud1 = ActivityBudget::create([
            'activity_id' => $actDiklat->id,
            'budget_category' => 'goods_services',
            'account_code' => '521811',
            'account_name' => 'Belanja Barang Persediaan Konsumsi (BLU)',
            'description' => 'Penyediaan Bahan Habis Pakai Ujian & Modul Ajar (BLU)',
            'amount' => 1881841000.0, // Rp 1.881.841.000 (from Excel row reference DL3996.DCB)
            'fiscal_year_id' => $fy2026->id,
            'version' => 1,
        ]);

        $bud2 = ActivityBudget::create([
            'activity_id' => $actDiklat->id,
            'budget_category' => 'other',
            'account_code' => '522131',
            'account_name' => 'Belanja Jasa Pemateri & Instruktur (RM)',
            'description' => 'Belanja Jasa Pemateri & Instruktur Luar Poltekpel (RM)',
            'amount' => 1048716000.0, // Rp 1.048.716.000
            'fiscal_year_id' => $fy2026->id,
            'version' => 1,
        ]);

        // Budgets for Pemeliharaan Activity
        $bud3 = ActivityBudget::create([
            'activity_id' => $actPemeliharaan->id,
            'budget_category' => 'capital',
            'account_code' => '532111',
            'account_name' => 'Belanja Modal Gedung & Bangunan (BLU)',
            'description' => 'Pengecatan & Maintenance Fisik Gedung Simulator (BLU)',
            'amount' => 133145000.0, // Rp 133.145.000
            'fiscal_year_id' => $fy2026->id,
            'version' => 1,
        ]);

        // 8. Seed Budget Realizations
        // Realization 1: Pembayaran Vendor Cetak Modul
        BudgetRealization::create([
            'activity_budget_id' => $bud1->id,
            'realization_type' => 'surat_pesanan',
            'amount' => 850000000.0,
            'realization_date' => '2026-03-15',
            'description' => 'Pembayaran termin 1 pengadaan modul pembelajaran taruna angkatan 70',
            'receipt_number' => 'REC-20260315-01',
            'vendor_name' => 'CV. Barombong Bahari Jaya',
            'vendor_address' => 'Jl. Pelayaran No. 12, Barombong, Makassar',
            'vendor_npwp' => '01.234.567.8-901.000',
            'procurement_number' => 'SP/DIKLAT/2026/001',
            'procurement_date' => '2026-03-01',
            'sp2d_number' => 'SP2D/394161/2026/099',
            'sp2d_date' => '2026-03-12',
            'verified_by' => $plannerUser->id,
            'verified_at' => '2026-03-16 10:00:00',
        ]);

        // Realization 2: Honor Instruktur Bulan Maret-April
        BudgetRealization::create([
            'activity_budget_id' => $bud2->id,
            'realization_type' => 'non_pengadaan',
            'amount' => 242407064.0,
            'realization_date' => '2026-04-30',
            'description' => 'Pembayaran honor instruktur diklat peningkatan kompetensi laut gelombang 1',
            'receipt_number' => 'REC-20260430-14',
            'verified_by' => $plannerUser->id,
            'verified_at' => '2026-05-02 09:15:00',
        ]);

        // Realization 3: Pembayaran Kalibrasi Bridge Simulator
        BudgetRealization::create([
            'activity_budget_id' => $bud3->id,
            'realization_type' => 'surat_pesanan',
            'amount' => 83694000.0,
            'realization_date' => '2026-05-18',
            'description' => 'Biaya termin 1 kalibrasi radar & simulator kemudi kapal',
            'receipt_number' => 'REC-20260518-05',
            'vendor_name' => 'PT. Simulator Global Indonesia',
            'vendor_address' => 'Ruko Techno Block A No. 5, Sudirman, Jakarta',
            'vendor_npwp' => '02.555.666.7-999.000',
            'procurement_number' => 'SP/SIMULATOR/2026/045',
            'procurement_date' => '2026-05-05',
            'sp2d_number' => 'SP2D/394161/2026/184',
            'sp2d_date' => '2026-05-15',
            'verified_by' => $plannerUser->id,
            'verified_at' => '2026-05-20 14:00:00',
        ]);

        // 9. Seed Activity Indicators (IKU/IKK)
        ActivityIndicator::create([
            'activity_id' => $actDiklat->id,
            'code' => 'IKK-DK-01',
            'name' => 'Jumlah taruna tingkat penjenjangan lulus UKP tepat waktu',
            'indicator_type' => 'ikk',
            'target_value' => 120.0,
            'actual_value' => 95.0,
            'unit_of_measure' => 'orang',
            'quarter' => 'annual',
        ]);

        ActivityIndicator::create([
            'activity_id' => $actPemeliharaan->id,
            'code' => 'IKK-PH-01',
            'name' => 'Tingkat kesiapan fungsional simulator utama pelayaran',
            'indicator_type' => 'ikk',
            'target_value' => 98.0,
            'actual_value' => 95.0,
            'unit_of_measure' => 'persen',
            'quarter' => 'Q2',
        ]);
    }
}
