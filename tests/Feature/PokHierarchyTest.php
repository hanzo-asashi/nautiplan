<?php

use App\Models\Activity;
use App\Models\ActivityBudget;
use App\Models\BudgetItem;
use App\Models\Component;
use App\Models\FiscalYear;
use App\Models\Output;
use App\Models\Program;
use App\Models\SubComponent;
use App\Models\SubOutput;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\FiscalYearSeeder;
use Database\Seeders\PokDataSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pok models relations work correctly', function () {
    $user = User::factory()->create();
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
        'name' => 'Test Item',
        'volume' => 10,
        'unit' => 'Pcs',
        'unit_price' => 5000,
        'total' => 50000,
    ]);

    // Assert relations
    expect($budgetItem->activityBudget->id)->toBe($budget->id);
    expect($budget->budgetItems)->toHaveCount(1);
    expect($budget->budgetItems->first()->name)->toBe('Test Item');
    expect($budget->subComponent->id)->toBe($subComponent->id);
    expect($subComponent->activityBudgets)->toHaveCount(1);
    expect($subComponent->component->id)->toBe($component->id);
    expect($component->subComponents)->toHaveCount(1);
    expect($component->subOutput->id)->toBe($subOutput->id);
    expect($subOutput->components)->toHaveCount(1);
    expect($subOutput->output->id)->toBe($output->id);
    expect($output->subOutputs)->toHaveCount(1);
    expect($output->activity->id)->toBe($activity->id);
});

test('pok data seeder runs successfully and parses excel', function () {
    // Seed initial dependencies first (Role, Unit, FiscalYear, DemoData)
    $this->seed(RoleSeeder::class);
    $this->seed(UnitSeeder::class);
    $this->seed(FiscalYearSeeder::class);
    $this->seed(DemoDataSeeder::class);

    // Run the POK Seeder
    $this->seed(PokDataSeeder::class);

    // Verify Output & SubOutput tables have records
    expect(Output::count())->toBeGreaterThan(0);
    expect(SubOutput::count())->toBeGreaterThan(0);
    expect(Component::count())->toBeGreaterThan(0);
    expect(SubComponent::count())->toBeGreaterThan(0);
    expect(BudgetItem::count())->toBeGreaterThan(0);

    // Verify a known item was seeded from sheet BLU REV 5
    $item = BudgetItem::where('name', 'like', '%Asuransi%')->first();
    expect($item)->not->toBeNull();
    expect($item->activityBudget)->not->toBeNull();
    expect($item->activityBudget->subComponent)->not->toBeNull();
});
