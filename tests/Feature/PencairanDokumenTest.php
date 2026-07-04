<?php

use App\Models\Activity;
use App\Models\ActivityBudget;
use App\Models\BudgetItem;
use App\Models\BudgetRealization;
use App\Models\Component;
use App\Models\FiscalYear;
use App\Models\Output;
use App\Models\Program;
use App\Models\SubComponent;
use App\Models\SubOutput;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pencairan documents and taxes can be saved and downloaded', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $fy = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
    ]);

    $unit = Unit::create([
        'code' => 'UNT-TEST',
        'name' => 'Unit Test',
        'is_active' => true,
    ]);

    $program = Program::create([
        'code' => 'TEST-PROG',
        'name' => 'Test Program',
        'unit_id' => $unit->id,
        'fiscal_year_id' => $fy->id,
        'status' => 'active',
        'priority' => 'high',
        'total_budget' => 1000000,
        'created_by' => $user->id,
    ]);

    $activity = Activity::create([
        'code' => 'TEST-ACT',
        'name' => 'Test Activity',
        'program_id' => $program->id,
        'unit_id' => $unit->id,
        'fiscal_year_id' => $fy->id,
        'status' => 'approved',
        'priority' => 'high',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'progress_percentage' => 0,
    ]);

    $output = Output::create([
        'activity_id' => $activity->id,
        'code' => 'TEST-OUT',
        'name' => 'Test Output',
    ]);

    $subOutput = SubOutput::create([
        'output_id' => $output->id,
        'code' => 'TEST-SUBOUT',
        'name' => 'Test Sub Output',
    ]);

    $component = Component::create([
        'sub_output_id' => $subOutput->id,
        'code' => 'TEST-COMP',
        'name' => 'Test Component',
    ]);

    $subComponent = SubComponent::create([
        'component_id' => $component->id,
        'code' => 'TEST-SUBCOMP',
        'name' => 'Test Sub Component',
    ]);

    $budget = ActivityBudget::create([
        'activity_id' => $activity->id,
        'sub_component_id' => $subComponent->id,
        'budget_category' => 'goods_services',
        'account_code' => '525119',
        'account_name' => 'Test Account',
        'description' => 'Test Description',
        'amount' => 500000,
        'fiscal_year_id' => $fy->id,
        'version' => 1,
    ]);

    $budgetItem = BudgetItem::create([
        'activity_budget_id' => $budget->id,
        'name' => 'Item 1',
        'volume' => 10,
        'unit' => 'Pcs',
        'unit_price' => 50000,
        'total' => 500000,
    ]);

    $payload = [
        'activity_budget_id' => $budget->id,
        'realization_type' => 'surat_pesanan',
        'amount' => 100000,
        'realization_date' => '2026-07-03',
        'description' => 'Test Realization Description',
        'receipt_number' => 'RCT-999',
        'bast_number' => 'BAST-001',
        'bast_date' => '2026-07-03',
        'bap_number' => 'BAP-001',
        'bap_date' => '2026-07-03',
        'ba_penyerahan_number' => 'BAPEN-001',
        'ba_penyerahan_date' => '2026-07-03',
        'sp2d_number' => 'SP2D-001',
        'sp2d_date' => '2026-07-03',
        'spp_number' => 'SPP-001',
        'spp_date' => '2026-07-03',
        'spm_number' => 'SPM-001',
        'spm_date' => '2026-07-03',
        'sptjb_number' => 'SPTJB-001',
        'sptjb_date' => '2026-07-03',
        'procurement_type' => 'spk',
        'procurement_title' => 'Test SPK Procurement',
        'procurement_number' => 'PRC-001',
        'procurement_date' => '2026-07-03',
        'work_duration' => '5 (lima) Hari Kalender',
        'vendor_name' => 'CV Test Vendor',
        'vendor_npwp' => '12.345.678.9-000.000',
        'vendor_address' => 'Test Address',
        'bank_name' => 'Bank Mandiri',
        'bank_account_number' => '1234567890',
        'bank_account_name' => 'CV Test Vendor Account',
        'items' => [
            [
                'budget_item_id' => $budgetItem->id,
                'name' => 'Item 1',
                'volume' => 2,
                'unit' => 'Pcs',
                'unit_price' => 50000,
                'tax_pph21' => 0,
                'tax_pph21_mixed' => false,
                'tax_pph22' => 1500,
                'tax_pph23' => 2000,
                'tax_ppn' => 11000,
                'remarks' => 'Test remarks',
            ],
        ],
    ];

    $response = $this->post(route('budgets.realizations.store'), $payload);
    $response->assertRedirect();

    $realization = BudgetRealization::where('receipt_number', 'RCT-999')->first();
    expect($realization)->not->toBeNull();
    expect($realization->spp_number)->toBe('SPP-001');
    expect($realization->spm_number)->toBe('SPM-001');
    expect($realization->sptjb_number)->toBe('SPTJB-001');

    $item = $realization->items->first();
    expect($item->tax_ppn)->toEqual(11000);
    expect($item->tax_pph22)->toEqual(1500);
    expect($item->tax_pph23)->toEqual(2000);

    // Verify PDF preview downloads stream
    $sppResponse = $this->get(route('reports.realization.spp', $realization->id));
    $sppResponse->assertOk();
    $sppResponse->assertHeader('content-disposition', 'inline; filename=spp-SPP-001.pdf');

    $spmResponse = $this->get(route('reports.realization.spm', $realization->id));
    $spmResponse->assertOk();
    $spmResponse->assertHeader('content-disposition', 'inline; filename=spm-SPM-001.pdf');

    $sptjbResponse = $this->get(route('reports.realization.sptjb', $realization->id));
    $sptjbResponse->assertOk();
    $sptjbResponse->assertHeader('content-disposition', 'inline; filename=sptjb-SPTJB-001.pdf');

    $sspResponse = $this->get(route('reports.realization.ssp', ['realization' => $realization->id, 'type' => 'ppn']));
    $sspResponse->assertOk();
    $sspResponse->assertHeader('content-disposition', 'inline; filename=ssp-ppn-RCT-999.pdf');
});
