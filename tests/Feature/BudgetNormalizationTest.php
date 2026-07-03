<?php

use App\Models\Activity;
use App\Models\ActivityBudget;
use App\Models\BudgetRealization;
use App\Models\FiscalYear;
use App\Models\Procurement;
use App\Models\Program;
use App\Models\RealizationItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->fiscalYear = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
    ]);

    $this->unit = Unit::create([
        'code' => 'UNT-TEST',
        'name' => 'Unit Test',
        'is_active' => true,
    ]);

    $this->program = Program::create([
        'code' => 'PRG-TEST',
        'name' => 'Program Test',
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'created_by' => $this->user->id,
    ]);

    $this->activity = Activity::create([
        'code' => 'ACT-TEST',
        'name' => 'Activity Test',
        'program_id' => $this->program->id,
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'status' => 'draft',
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-10',
        'progress_percentage' => 0,
        'responsible_user_id' => $this->user->id,
    ]);

    $this->budget = ActivityBudget::create([
        'activity_id' => $this->activity->id,
        'budget_category' => 'goods_services',
        'account_code' => '525112',
        'account_name' => 'Belanja Bahan',
        'description' => 'Pembelian ATK',
        'amount' => 50000000,
        'fiscal_year_id' => $this->fiscalYear->id,
    ]);

    // Create another user as PPK/KPA
    $this->ppk = User::factory()->create(['name' => 'Arnaldy Achmadita', 'rank' => 'Penata (III/c)']);
    $this->kpa = User::factory()->create(['name' => 'Sidrotul Muntaha', 'rank' => 'Pembina Tk.I (IV/b)']);
});

test('guest cannot access budget realizations store endpoint', function () {
    $this->post(route('budgets.realizations.store'), [])
        ->assertRedirect(route('login'));
});

test('finance/admin user can view budget page with vendors and officers', function () {
    $response = $this->actingAs($this->user)->get(route('budgets.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('budgets/Index')
        ->has('budgets')
        ->has('vendors')
        ->has('officers')
    );
});

test('user can store a normalized procurement realization with nested items', function () {
    $payload = [
        'activity_budget_id' => $this->budget->id,
        'realization_type' => 'surat_pesanan',
        'amount' => 11100000, // 10,000,000 + 11% PPN
        'realization_date' => '2026-07-02',
        'description' => 'Belanja laptop dinas',
        'receipt_number' => 'KWT-001',

        'procurement_type' => 'spk',
        'procurement_title' => 'Pengadaan Laptop Kantor',
        'procurement_number' => 'SPK/123/2026',
        'procurement_date' => '2026-07-01',
        'work_duration' => '7 hari',
        'nota_dinas_number' => 'ND/11',
        'nota_dinas_date' => '2026-06-30',
        'ba_pl_number' => 'BAPL/99',
        'ba_pl_date' => '2026-07-01',
        'ppk_id' => $this->ppk->id,
        'kpa_id' => $this->kpa->id,

        'vendor_name' => 'CV. Teksas Jaya',
        'vendor_npwp' => '01.234.567.8-091.000',
        'vendor_address' => 'Jl. Barombong No. 10',
        'bank_name' => 'Bank BTN',
        'bank_account_number' => '987654321',
        'bank_account_name' => 'CV Teksas Jaya',

        'items' => [
            [
                'name' => 'Laptop Asus ROG',
                'volume' => 1,
                'unit' => 'Unit',
                'unit_price' => 10000000,
                'remarks' => 'Intel i7 16GB SSD 512GB',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->from(route('budgets.index'))
        ->post(route('budgets.realizations.store'), $payload);

    $response->assertRedirect(route('budgets.index'));

    // Assert Vendor was created
    $this->assertDatabaseHas('vendors', [
        'name' => 'CV. Teksas Jaya',
        'npwp' => '01.234.567.8-091.000',
        'bank_name' => 'Bank BTN',
    ]);

    $vendor = Vendor::where('name', 'CV. Teksas Jaya')->first();

    // Assert Procurement was created
    $this->assertDatabaseHas('procurements', [
        'vendor_id' => $vendor->id,
        'document_number' => 'SPK/123/2026',
        'procurement_type' => 'spk',
        'ppk_id' => $this->ppk->id,
        'kpa_id' => $this->kpa->id,
    ]);

    $procurement = Procurement::where('document_number', 'SPK/123/2026')->first();

    // Assert BudgetRealization was created
    $this->assertDatabaseHas('budget_realizations', [
        'activity_budget_id' => $this->budget->id,
        'procurement_id' => $procurement->id,
        'amount' => 11100000,
        'receipt_number' => 'KWT-001',
    ]);

    $realization = BudgetRealization::where('receipt_number', 'KWT-001')->first();

    // Assert RealizationItems were created
    $this->assertDatabaseHas('realization_items', [
        'budget_realization_id' => $realization->id,
        'name' => 'Laptop Asus ROG',
        'volume' => 1,
        'unit_price' => 10000000,
    ]);
});

test('user can download normalized PDFs', function () {
    // Setup a realization in database manually with relations
    $vendor = Vendor::create([
        'name' => 'CV. Jaya Wijaya',
        'npwp' => '12.345.678.9-012.000',
        'address' => 'Makassar',
        'bank_name' => 'BRI',
        'bank_account_number' => '123456',
        'bank_account_name' => 'Jaya Wijaya',
    ]);

    $procurement = Procurement::create([
        'activity_budget_id' => $this->budget->id,
        'vendor_id' => $vendor->id,
        'title' => 'Pengadaan ATK Kantor',
        'procurement_type' => 'surat_pesanan',
        'document_number' => 'SP/999/2026',
        'document_date' => '2026-07-01',
        'ppk_id' => $this->ppk->id,
        'kpa_id' => $this->kpa->id,
    ]);

    $realization = BudgetRealization::create([
        'activity_budget_id' => $this->budget->id,
        'procurement_id' => $procurement->id,
        'realization_type' => 'surat_pesanan',
        'amount' => 5000000,
        'realization_date' => '2026-07-02',
        'description' => 'Pembayaran ATK',
        'receipt_number' => 'KWT-999',
    ]);

    RealizationItem::create([
        'budget_realization_id' => $realization->id,
        'name' => 'Kertas A4',
        'volume' => 10,
        'unit' => 'Rim',
        'unit_price' => 500000,
    ]);

    // Test SP
    $response = $this->actingAs($this->user)->get(route('reports.realization.pdf', $realization));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    // Test SPK
    $response = $this->actingAs($this->user)->get(route('reports.realization.spk', $realization));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    // Test BAST
    $response = $this->actingAs($this->user)->get(route('reports.realization.bast', $realization));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    // Test BAP
    $response = $this->actingAs($this->user)->get(route('reports.realization.bap', $realization));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    // Test Kwitansi
    $response = $this->actingAs($this->user)->get(route('reports.realization.kwitansi', $realization));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

test('user can download PDFs even if the realization has no procurement', function () {
    $realization = BudgetRealization::create([
        'activity_budget_id' => $this->budget->id,
        'procurement_id' => null,
        'realization_type' => 'non_pengadaan',
        'amount' => 1500000,
        'realization_date' => '2026-07-02',
        'description' => 'Pembelian ATK Swakelola',
        'receipt_number' => 'KWT-999-SW',
    ]);

    // Test SPK
    $response = $this->actingAs($this->user)->get(route('reports.realization.spk', $realization));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    // Test BAST
    $response = $this->actingAs($this->user)->get(route('reports.realization.bast', $realization));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    // Test BAP
    $response = $this->actingAs($this->user)->get(route('reports.realization.bap', $realization));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    // Test Kwitansi
    $response = $this->actingAs($this->user)->get(route('reports.realization.kwitansi', $realization));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});
