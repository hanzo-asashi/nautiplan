<?php

use App\Models\Activity;
use App\Models\ActivityBudget;
use App\Models\BudgetItem;
use App\Models\BudgetRealization;
use App\Models\BudgetRevision;
use App\Models\BudgetRevisionDetail;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\RealizationItem;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'super-admin', 'display_name' => 'Super Admin']);
    $this->financeRole = Role::create(['name' => 'keuangan', 'display_name' => 'Bagian Keuangan']);

    // Create unit
    $this->unit = Unit::create(['name' => 'Teknologi Informasi', 'code' => 'TI']);

    // Create fiscal year
    $this->fiscalYear = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
        'is_locked' => false,
    ]);

    // Create user with finance role
    $this->user = User::factory()->create([
        'unit_id' => $this->unit->id,
    ]);
    $this->user->roles()->attach($this->financeRole);

    // Create program
    $this->program = Program::create([
        'code' => '01',
        'name' => 'Program Utama',
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'created_by' => $this->user->id,
    ]);

    // Create activity
    $this->activity = Activity::create([
        'code' => '001',
        'name' => 'Diklat Pemrograman Web',
        'program_id' => $this->program->id,
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'status' => 'draft',
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-10',
        'progress_percentage' => 0,
        'responsible_user_id' => $this->user->id,
    ]);

    // Create budget
    $this->budget = ActivityBudget::create([
        'activity_id' => $this->activity->id,
        'budget_category' => 'goods_services',
        'account_code' => '521211',
        'account_name' => 'Belanja Bahan',
        'description' => 'Pembelian ATK Simulator',
        'amount' => 10000000,
        'fiscal_year_id' => $this->fiscalYear->id,
    ]);

    // Create budget item
    $this->budgetItem = BudgetItem::create([
        'activity_budget_id' => $this->budget->id,
        'name' => 'Kertas A4 80gr',
        'volume' => 10,
        'unit' => 'Rim',
        'unit_price' => 100000,
        'total' => 1000000,
    ]);
});

test('prevent realization item markup price', function () {
    $this->activity->update(['status' => 'approved']);
    $payload = [
        'activity_budget_id' => $this->budget->id,
        'realization_type' => 'non_pengadaan',
        'amount' => 150000,
        'realization_date' => '2026-07-02',
        'description' => 'Pembelian ATK Realisasi',
        'receipt_number' => 'KWT-001',
        'items' => [
            [
                'budget_item_id' => $this->budgetItem->id,
                'name' => 'Kertas A4 80gr Premium',
                'volume' => 1,
                'unit' => 'Rim',
                'unit_price' => 150000, // Exceeds budget plan unit_price of 100.000!
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->post(route('budgets.realizations.store'), $payload);

    $response->assertSessionHasErrors('items');
    $this->assertDatabaseMissing('budget_realizations', [
        'receipt_number' => 'KWT-001',
    ]);
});

test('prevent realization item exceeding remaining volume', function () {
    $this->activity->update(['status' => 'approved']);
    $payload = [
        'activity_budget_id' => $this->budget->id,
        'realization_type' => 'non_pengadaan',
        'amount' => 1200000,
        'realization_date' => '2026-07-02',
        'description' => 'Pembelian ATK Realisasi',
        'receipt_number' => 'KWT-002',
        'items' => [
            [
                'budget_item_id' => $this->budgetItem->id,
                'name' => 'Kertas A4 80gr Premium',
                'volume' => 12, // Exceeds budget plan volume of 10!
                'unit' => 'Rim',
                'unit_price' => 100000,
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->post(route('budgets.realizations.store'), $payload);

    $response->assertSessionHasErrors('items');
    $this->assertDatabaseMissing('budget_realizations', [
        'receipt_number' => 'KWT-002',
    ]);
});

test('user can revise POK budget and record Semula vs Menjadi history', function () {
    $payload = [
        'budget_category' => 'goods_services',
        'account_code' => '521211',
        'account_name' => 'Belanja Bahan',
        'description' => 'Pembelian ATK Simulator',
        'revision_description' => 'Pergeseran volume kertas karena kebutuhan bertambah',
        'items' => [
            [
                'id' => $this->budgetItem->id,
                'name' => 'Kertas A4 80gr',
                'volume' => 15, // Volume changed from 10 to 15
                'unit' => 'Rim',
                'unit_price' => 100000,
            ],
            [
                'name' => 'Tinta Printer EPSON', // New item added
                'volume' => 2,
                'unit' => 'Botol',
                'unit_price' => 150000,
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('budgets.update', $this->budget), $payload);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    // Verify budget version incremented and amount updated
    $this->budget->refresh();
    expect($this->budget->version)->toBe(2);
    expect((float) $this->budget->amount)->toBe(1800000.0); // (15 * 100000) + (2 * 150000) = 1.5M + 300K = 1.8M

    // Verify revision is created
    $this->assertDatabaseHas('budget_revisions', [
        'activity_budget_id' => $this->budget->id,
        'revision_number' => 1,
        'description' => 'Pergeseran volume kertas karena kebutuhan bertambah',
        'amount_semula' => 10000000.0, // Initial budget amount
        'amount_menjadi' => 1800000.0,
        'revised_by' => $this->user->id,
    ]);

    $revision = BudgetRevision::where('activity_budget_id', $this->budget->id)->first();

    // Verify revision details
    // 1. Modified item
    $this->assertDatabaseHas('budget_revision_details', [
        'budget_revision_id' => $revision->id,
        'budget_item_id' => $this->budgetItem->id,
        'name_semula' => 'Kertas A4 80gr',
        'volume_semula' => 10.0,
        'unit_price_semula' => 100000.0,
        'total_semula' => 1000000.0,
        'name_menjadi' => 'Kertas A4 80gr',
        'volume_menjadi' => 15.0,
        'unit_price_menjadi' => 100000.0,
        'total_menjadi' => 1500000.0,
    ]);

    // 2. New item details
    $this->assertDatabaseHas('budget_revision_details', [
        'budget_revision_id' => $revision->id,
        'name_semula' => null,
        'volume_semula' => 0.0,
        'name_menjadi' => 'Tinta Printer EPSON',
        'volume_menjadi' => 2.0,
        'unit_price_menjadi' => 150000.0,
        'total_menjadi' => 300000.0,
    ]);
});

test('user can download PDF revision comparative report', function () {
    $revision = BudgetRevision::create([
        'activity_budget_id' => $this->budget->id,
        'revision_number' => 1,
        'description' => 'Revisi Contoh',
        'amount_semula' => 10000000.0,
        'amount_menjadi' => 5000000.0,
        'revised_by' => $this->user->id,
    ]);

    BudgetRevisionDetail::create([
        'budget_revision_id' => $revision->id,
        'budget_item_id' => $this->budgetItem->id,
        'name_semula' => 'Kertas A4 80gr',
        'volume_semula' => 10.0,
        'unit_semula' => 'Rim',
        'unit_price_semula' => 100000.0,
        'total_semula' => 1000000.0,
        'name_menjadi' => 'Kertas A4 80gr',
        'volume_menjadi' => 5.0,
        'unit_menjadi' => 'Rim',
        'unit_price_menjadi' => 100000.0,
        'total_menjadi' => 500000.0,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('reports.revision.pdf', $revision));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

test('prevent realization if activity is not approved', function () {
    // Activity status is draft by default in setup
    $payload = [
        'activity_budget_id' => $this->budget->id,
        'realization_type' => 'non_pengadaan',
        'amount' => 100000,
        'realization_date' => '2026-07-02',
        'description' => 'Pembelian ATK Realisasi',
        'receipt_number' => 'KWT-999',
        'items' => [
            [
                'budget_item_id' => $this->budgetItem->id,
                'name' => 'Kertas A4 80gr',
                'volume' => 1,
                'unit' => 'Rim',
                'unit_price' => 100000,
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->post(route('budgets.realizations.store'), $payload);

    $response->assertSessionHasErrors('activity_budget_id');
});

test('prevent POK item deletion if it has realizations', function () {
    $this->activity->update(['status' => 'approved']);

    // Create a realization for the item first
    $realization = BudgetRealization::create([
        'activity_budget_id' => $this->budget->id,
        'realization_type' => 'non_pengadaan',
        'amount' => 100000,
        'realization_date' => '2026-07-02',
        'description' => 'Pembelian ATK Realisasi',
        'receipt_number' => 'KWT-999',
    ]);

    RealizationItem::create([
        'budget_realization_id' => $realization->id,
        'budget_item_id' => $this->budgetItem->id,
        'name' => 'Kertas A4 80gr',
        'volume' => 1,
        'unit' => 'Rim',
        'unit_price' => 100000,
        'total' => 100000,
    ]);

    // Try to delete the item during budget update (POK revision)
    // Payload omits the item id (which means delete)
    $payload = [
        'budget_category' => 'goods_services',
        'account_code' => '521211',
        'account_name' => 'Belanja Bahan',
        'description' => 'Pembelian ATK Simulator',
        'revision_description' => 'Revisi menghapus item terrealisasi',
        'items' => [
            [
                'name' => 'Tinta Printer EPSON',
                'volume' => 2,
                'unit' => 'Botol',
                'unit_price' => 150000,
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('budgets.update', $this->budget), $payload);

    $response->assertSessionHasErrors('items');
});

test('POK revision resets activity status to draft', function () {
    $this->activity->update(['status' => 'approved']);

    $payload = [
        'budget_category' => 'goods_services',
        'account_code' => '521211',
        'account_name' => 'Belanja Bahan',
        'description' => 'Pembelian ATK Simulator',
        'revision_description' => 'Revisi mengubah volume',
        'items' => [
            [
                'id' => $this->budgetItem->id,
                'name' => 'Kertas A4 80gr',
                'volume' => 12,
                'unit' => 'Rim',
                'unit_price' => 100000,
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->put(route('budgets.update', $this->budget), $payload);

    $response->assertRedirect();

    $this->activity->refresh();
    expect($this->activity->status)->toBe('draft');
});
