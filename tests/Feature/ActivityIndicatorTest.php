<?php

use App\Models\Activity;
use App\Models\ActivityIndicator;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Unit;
use App\Models\User;

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

test('guest cannot create indicator', function () {
    $response = $this->post(route('activities.indicators.store', $this->activity->id), [
        'code' => 'IND-01',
        'name' => 'Test Indicator',
        'indicator_type' => 'ikk',
        'target_value' => 100,
        'unit_of_measure' => 'persen',
        'quarter' => 'annual',
    ]);

    $response->assertRedirect(route('login'));
});

test('authenticated user can create indicator with valid data', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('activities.indicators.store', $this->activity->id), [
        'code' => 'IND-01',
        'name' => 'Test Indicator',
        'indicator_type' => 'ikk',
        'target_value' => 100,
        'unit_of_measure' => 'persen',
        'quarter' => 'annual',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    $this->assertDatabaseHas('activity_indicators', [
        'activity_id' => $this->activity->id,
        'code' => 'IND-01',
        'name' => 'Test Indicator',
        'indicator_type' => 'ikk',
        'target_value' => 100.00,
        'unit_of_measure' => 'persen',
        'quarter' => 'annual',
    ]);
});

test('indicator code must be unique per activity and quarter', function () {
    $this->actingAs($this->user);

    ActivityIndicator::create([
        'activity_id' => $this->activity->id,
        'code' => 'IND-01',
        'name' => 'Existing Indicator',
        'indicator_type' => 'ikk',
        'target_value' => 100,
        'unit_of_measure' => 'persen',
        'quarter' => 'Q1',
    ]);

    $response = $this->post(route('activities.indicators.store', $this->activity->id), [
        'code' => 'IND-01',
        'name' => 'New Indicator',
        'indicator_type' => 'ikk',
        'target_value' => 90,
        'unit_of_measure' => 'persen',
        'quarter' => 'Q1',
    ]);

    $response->assertSessionHasErrors('code');
});

test('authenticated user can update indicator', function () {
    $this->actingAs($this->user);

    $indicator = ActivityIndicator::create([
        'activity_id' => $this->activity->id,
        'code' => 'IND-01',
        'name' => 'Old Name',
        'indicator_type' => 'ikk',
        'target_value' => 100,
        'unit_of_measure' => 'persen',
        'quarter' => 'annual',
    ]);

    $response = $this->put(route('activities.indicators.update', $indicator->id), [
        'code' => 'IND-01',
        'name' => 'New Name',
        'indicator_type' => 'iku',
        'target_value' => 80,
        'actual_value' => 40,
        'unit_of_measure' => 'dokumen',
        'quarter' => 'annual',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('activity_indicators', [
        'id' => $indicator->id,
        'name' => 'New Name',
        'indicator_type' => 'iku',
        'target_value' => 80.00,
        'actual_value' => 40.00,
        'unit_of_measure' => 'dokumen',
    ]);
});

test('authenticated user can delete indicator', function () {
    $this->actingAs($this->user);

    $indicator = ActivityIndicator::create([
        'activity_id' => $this->activity->id,
        'code' => 'IND-01',
        'name' => 'To Delete',
        'indicator_type' => 'ikk',
        'target_value' => 100,
        'unit_of_measure' => 'persen',
        'quarter' => 'annual',
    ]);

    $response = $this->delete(route('activities.indicators.destroy', $indicator->id));

    $response->assertRedirect();
    $this->assertModelMissing($indicator);
});

test('authenticated user can create activity with indicators inline', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('activities.store'), [
        'code' => 'ACT-NEW',
        'name' => 'New Activity inline',
        'program_id' => $this->program->id,
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'status' => 'draft',
        'priority' => 'medium',
        'indicators' => [
            [
                'code' => 'IND-01',
                'name' => 'Indicator 1',
                'indicator_type' => 'ikk',
                'target_value' => 100,
                'unit_of_measure' => 'persen',
                'quarter' => 'annual',
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    $this->assertDatabaseHas('activity_indicators', [
        'code' => 'IND-01',
        'name' => 'Indicator 1',
    ]);
});

test('authenticated user can update activity with indicators inline', function () {
    $this->actingAs($this->user);

    $indicator = ActivityIndicator::create([
        'activity_id' => $this->activity->id,
        'code' => 'IND-01',
        'name' => 'Old Name',
        'indicator_type' => 'ikk',
        'target_value' => 100,
        'unit_of_measure' => 'persen',
        'quarter' => 'annual',
    ]);

    $response = $this->put(route('activities.update', $this->activity->id), [
        'code' => $this->activity->code,
        'name' => 'Updated Activity',
        'program_id' => $this->program->id,
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'status' => 'draft',
        'priority' => 'medium',
        'progress_percentage' => 10,
        'indicators' => [
            [
                'id' => $indicator->id,
                'code' => 'IND-01',
                'name' => 'Updated Name',
                'indicator_type' => 'ikk',
                'target_value' => 90,
                'actual_value' => 50,
                'unit_of_measure' => 'persen',
                'quarter' => 'annual',
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    $this->assertDatabaseHas('activity_indicators', [
        'id' => $indicator->id,
        'name' => 'Updated Name',
        'actual_value' => 50.00,
    ]);
});
