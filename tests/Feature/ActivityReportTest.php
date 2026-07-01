<?php

use App\Models\Activity;
use App\Models\ActivityReport;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->unit = Unit::create([
        'code' => 'UNIT-REP',
        'name' => 'Unit Report Test',
        'is_active' => true,
    ]);

    $this->fiscalYear = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
    ]);

    $this->program = Program::create([
        'code' => 'PROG-REP',
        'name' => 'Program Report Test',
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'created_by' => $this->user->id,
    ]);

    $this->activity = Activity::create([
        'code' => 'ACT-REP',
        'name' => 'Activity Report Test',
        'program_id' => $this->program->id,
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
    ]);
});

test('guest cannot access activity reports', function () {
    $this->get(route('monitoring.reports.index'))
        ->assertRedirect(route('login'));

    $this->get(route('monitoring.reports.show', ['activity' => $this->activity->id, 'quarter' => 'Q1']))
        ->assertRedirect(route('login'));
});

test('authenticated user can view report index and form', function () {
    $this->actingAs($this->user);

    $this->get(route('monitoring.reports.index'))
        ->assertOk();

    $this->get(route('monitoring.reports.show', ['activity' => $this->activity->id, 'quarter' => 'Q1']))
        ->assertOk();
});

test('operator can save draft report', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('monitoring.reports.store', ['activity' => $this->activity->id, 'quarter' => 'Q1']), [
        'status' => 'draft',
        'progress_description' => 'Dalam pengerjaan draf',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('activity_reports', [
        'activity_id' => $this->activity->id,
        'quarter' => 'Q1',
        'status' => 'draft',
        'progress_description' => 'Dalam pengerjaan draf',
    ]);
});

test('operator must fill all required fields when submitting report', function () {
    $this->actingAs($this->user);

    // Fail validation
    $response = $this->post(route('monitoring.reports.store', ['activity' => $this->activity->id, 'quarter' => 'Q1']), [
        'status' => 'submitted',
        'progress_description' => '',
    ]);

    $response->assertSessionHasErrors(['progress_description', 'obstacles', 'solutions']);

    // Successful submit
    $response = $this->post(route('monitoring.reports.store', ['activity' => $this->activity->id, 'quarter' => 'Q1']), [
        'status' => 'submitted',
        'progress_description' => 'Realisasi fisik 100%',
        'obstacles' => 'Tidak ada kendala',
        'solutions' => 'Tetap konsisten',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('activity_reports', [
        'activity_id' => $this->activity->id,
        'quarter' => 'Q1',
        'status' => 'submitted',
        'progress_description' => 'Realisasi fisik 100%',
    ]);
});

test('regular user cannot submit evaluation', function () {
    $this->actingAs($this->user);

    $report = ActivityReport::create([
        'activity_id' => $this->activity->id,
        'quarter' => 'Q1',
        'status' => 'submitted',
        'progress_description' => 'Laporan dari unit',
    ]);

    $response = $this->post(route('monitoring.reports.evaluate', ['activity' => $this->activity->id, 'quarter' => 'Q1']), [
        'evaluation_score' => 90,
        'evaluation_notes' => 'Catatan bagus',
        'recommendations' => 'Rekomendasi lanjut',
    ]);

    $response->assertStatus(403);
});

test('admin can submit evaluation on a submitted report', function () {
    $adminUser = User::factory()->create();
    $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
    $adminUser->roles()->attach($adminRole->id, ['assigned_at' => now(), 'assigned_by' => 1]);

    $this->actingAs($adminUser);

    $report = ActivityReport::create([
        'activity_id' => $this->activity->id,
        'quarter' => 'Q1',
        'status' => 'submitted',
        'progress_description' => 'Laporan dari unit',
    ]);

    $response = $this->post(route('monitoring.reports.evaluate', ['activity' => $this->activity->id, 'quarter' => 'Q1']), [
        'evaluation_score' => 95,
        'evaluation_notes' => 'Sangat memuaskan',
        'recommendations' => 'Pertahankan kinerja',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('activity_reports', [
        'activity_id' => $this->activity->id,
        'quarter' => 'Q1',
        'status' => 'reviewed',
        'evaluation_score' => 95,
        'evaluation_notes' => 'Sangat memuaskan',
    ]);
});
