<?php

use App\Models\Activity;
use App\Models\ActivityIndicator;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Unit;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->unit = Unit::create([
        'code' => 'UNIT-TEST',
        'name' => 'Unit Test',
        'is_active' => true,
    ]);

    $this->fiscalYear = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
    ]);

    $this->program = Program::create([
        'code' => 'PROG-TEST',
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
    ]);
});

test('guest cannot access kpi dashboard', function () {
    $response = $this->get(route('monitoring.kpi'));
    $response->assertRedirect(route('login'));
});

test('authenticated user can access kpi dashboard', function () {
    $this->actingAs($this->user);

    ActivityIndicator::create([
        'activity_id' => $this->activity->id,
        'code' => 'IND-01',
        'name' => 'Test Indicator IKU',
        'indicator_type' => 'iku',
        'target_value' => 100,
        'actual_value' => 80,
        'unit_of_measure' => 'persen',
        'quarter' => 'annual',
    ]);

    $response = $this->get(route('monitoring.kpi'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('monitoring/KpiDashboard')
        ->has('stats', fn (Assert $stats) => $stats
            ->where('total_indicators', 1)
            ->where('total_iku', 1)
            ->where('total_ikk', 0)
            ->where('achieved_count', 0)
            ->where('in_progress_count', 1)
            ->where('no_progress_count', 0)
            ->where('average_achievement', 80)
        )
        ->has('quarter_stats')
        ->has('unit_stats')
        ->has('indicators')
    );
});
