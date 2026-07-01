<?php

use App\Models\Activity;
use App\Models\ApprovalRequest;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->user = User::factory()->create();

    // Create roles
    $this->roleOperator = Role::create(['name' => 'operator-unit', 'display_name' => 'Operator Unit']);
    $this->roleKabag = Role::create(['name' => 'kepala-bagian', 'display_name' => 'Kepala Bagian']);
    $this->roleWadir = Role::create(['name' => 'wakil-direktur', 'display_name' => 'Wakil Direktur']);
    $this->roleDirektur = Role::create(['name' => 'direktur', 'display_name' => 'Direktur']);

    $this->unit = Unit::create([
        'code' => 'UNIT-APP',
        'name' => 'Unit Approval Test',
        'is_active' => true,
    ]);

    $this->fiscalYear = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
    ]);

    $this->program = Program::create([
        'code' => 'PROG-APP',
        'name' => 'Program Approval Test',
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'created_by' => $this->user->id,
    ]);

    $this->activity = Activity::create([
        'code' => 'ACT-APP',
        'name' => 'Activity Approval Test',
        'program_id' => $this->program->id,
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'status' => 'draft',
    ]);
});

test('guest cannot access approvals', function () {
    $this->get(route('approvals.index'))
        ->assertRedirect(route('login'));
});

test('operator can submit activity for approval', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('activities.submit-approval', ['activity' => $this->activity->id]));

    $response->assertRedirect();
    $this->assertDatabaseHas('activities', [
        'id' => $this->activity->id,
        'status' => 'proposed',
    ]);

    $this->assertDatabaseHas('approval_requests', [
        'approvable_type' => Activity::class,
        'approvable_id' => $this->activity->id,
        'status' => 'pending',
        'current_step' => 1,
    ]);

    $approvalRequest = ApprovalRequest::where('approvable_id', $this->activity->id)->first();
    $this->assertCount(3, $approvalRequest->steps);
});

test('unauthorized role cannot act on current step', function () {
    $this->actingAs($this->user);

    // Submit first
    $this->post(route('activities.submit-approval', ['activity' => $this->activity->id]));

    $approvalRequest = ApprovalRequest::where('approvable_id', $this->activity->id)->first();

    // Act without role
    $response = $this->post(route('approvals.action', ['approvalRequest' => $approvalRequest->id]), [
        'status' => 'approved',
    ]);

    $response->assertStatus(403);
});

test('full multi step approval chain success flow', function () {
    // 1. Submit proposal
    $this->actingAs($this->user);
    $this->post(route('activities.submit-approval', ['activity' => $this->activity->id]));
    $approvalRequest = ApprovalRequest::where('approvable_id', $this->activity->id)->first();

    // 2. Step 1 (Kepala Bagian) approvals
    $kabagUser = User::factory()->create();
    $kabagUser->roles()->attach($this->roleKabag->id, ['assigned_at' => now(), 'assigned_by' => 1]);
    $this->actingAs($kabagUser);

    $response = $this->post(route('approvals.action', ['approvalRequest' => $approvalRequest->id]), [
        'status' => 'approved',
        'notes' => 'Kabag setuju',
    ]);
    $response->assertRedirect();
    $this->assertEquals(2, $approvalRequest->fresh()->current_step);

    // 3. Step 2 (Wakil Direktur) approvals
    $wadirUser = User::factory()->create();
    $wadirUser->roles()->attach($this->roleWadir->id, ['assigned_at' => now(), 'assigned_by' => 1]);
    $this->actingAs($wadirUser);

    $response = $this->post(route('approvals.action', ['approvalRequest' => $approvalRequest->id]), [
        'status' => 'approved',
        'notes' => 'Wadir setuju',
    ]);
    $response->assertRedirect();
    $this->assertEquals(3, $approvalRequest->fresh()->current_step);

    // 4. Step 3 (Direktur) approvals -> fully approved
    $direkturUser = User::factory()->create();
    $direkturUser->roles()->attach($this->roleDirektur->id, ['assigned_at' => now(), 'assigned_by' => 1]);
    $this->actingAs($direkturUser);

    $response = $this->post(route('approvals.action', ['approvalRequest' => $approvalRequest->id]), [
        'status' => 'approved',
        'notes' => 'Direktur setuju, mantap!',
    ]);
    $response->assertRedirect();

    $this->assertEquals('approved', $approvalRequest->fresh()->status);
    $this->assertEquals('approved', $this->activity->fresh()->status);
});

test('reviewer can request revision and operator can resubmit', function () {
    // 1. Submit proposal
    $this->actingAs($this->user);
    $this->post(route('activities.submit-approval', ['activity' => $this->activity->id]));
    $approvalRequest = ApprovalRequest::where('approvable_id', $this->activity->id)->first();

    // 2. Step 1 (Kepala Bagian) requests revision
    $kabagUser = User::factory()->create();
    $kabagUser->roles()->attach($this->roleKabag->id, ['assigned_at' => now(), 'assigned_by' => 1]);
    $this->actingAs($kabagUser);

    $response = $this->post(route('approvals.action', ['approvalRequest' => $approvalRequest->id]), [
        'status' => 'revision',
        'notes' => 'Tolong lengkapi sub kegiatan',
    ]);
    $response->assertRedirect();

    $this->assertEquals('revision', $approvalRequest->fresh()->status);
    $this->assertEquals('draft', $this->activity->fresh()->status);
});

test('operator cannot edit activity while under review', function () {
    $this->actingAs($this->user);

    // Submit for review
    $this->post(route('activities.submit-approval', ['activity' => $this->activity->id]));

    // Try editing -> should get 403
    $this->get(route('activities.edit', ['activity' => $this->activity->id]))
        ->assertStatus(403);

    $this->put(route('activities.update', ['activity' => $this->activity->id]), [
        'code' => $this->activity->code,
        'name' => 'New Name Attempt',
        'program_id' => $this->program->id,
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'status' => 'proposed',
        'priority' => 'medium',
        'progress_percentage' => 0,
    ])->assertStatus(403);
});
