<?php

use App\Models\Activity;
use App\Models\ActivityDocument;
use App\Models\FiscalYear;
use App\Models\Notification;
use App\Models\Program;
use App\Models\Role;
use App\Models\SubActivity;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create units
    $this->unit1 = Unit::create(['code' => 'UNT-A', 'name' => 'Unit A', 'is_active' => true]);
    $this->unit2 = Unit::create(['code' => 'UNT-B', 'name' => 'Unit B', 'is_active' => true]);

    // Create users
    $this->authorizedUser = User::factory()->create(['unit_id' => $this->unit1->id]);
    $this->unauthorizedUser = User::factory()->create(['unit_id' => $this->unit2->id]);

    // Create admin user
    $this->adminUser = User::factory()->create();
    $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
    $this->adminUser->roles()->attach($adminRole->id, ['assigned_at' => now()]);

    $this->fiscalYear = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
    ]);

    $this->program = Program::create([
        'code' => 'PRG-TEST',
        'name' => 'Program Test',
        'unit_id' => $this->unit1->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'created_by' => $this->authorizedUser->id,
    ]);

    $this->activity = Activity::create([
        'code' => 'ACT-TEST',
        'name' => 'Activity Test',
        'program_id' => $this->program->id,
        'unit_id' => $this->unit1->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'status' => 'draft',
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-10',
        'progress_percentage' => 0,
        'responsible_user_id' => $this->authorizedUser->id,
        'priority' => 'medium',
    ]);

    $this->subActivity = SubActivity::create([
        'activity_id' => $this->activity->id,
        'name' => 'Sub Activity Test',
        'status' => 'pending',
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-05',
        'progress_percentage' => 0,
        'assigned_to' => $this->authorizedUser->id,
    ]);
});

it('restricts kanban board access to authorized users', function () {
    // Unauthorized user should be forbidden
    $this->actingAs($this->unauthorizedUser)
        ->get(route('activities.kanban', $this->activity))
        ->assertStatus(403);

    // Authorized user should be allowed
    $this->actingAs($this->authorizedUser)
        ->get(route('activities.kanban', $this->activity))
        ->assertStatus(200);

    // Admin should be allowed
    $this->actingAs($this->adminUser)
        ->get(route('activities.kanban', $this->activity))
        ->assertStatus(200);
});

it('restricts revisions access to authorized users', function () {
    // Unauthorized user should be forbidden
    $this->actingAs($this->unauthorizedUser)
        ->get(route('activities.revisions', $this->activity))
        ->assertStatus(403);

    // Authorized user should be allowed
    $this->actingAs($this->authorizedUser)
        ->get(route('activities.revisions', $this->activity))
        ->assertStatus(200);
});

it('restricts sub activity status updates to authorized users', function () {
    // Unauthorized user should be forbidden
    $this->actingAs($this->unauthorizedUser)
        ->put(route('sub-activities.update-status', $this->subActivity), [
            'status' => 'in_progress',
            'progress_percentage' => 50,
        ])
        ->assertStatus(403);

    // Authorized user should be allowed
    $this->actingAs($this->authorizedUser)
        ->put(route('sub-activities.update-status', $this->subActivity), [
            'status' => 'in_progress',
            'progress_percentage' => 50,
        ])
        ->assertRedirect();
});

it('restricts document upload to authorized users', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('document.pdf', 100);

    // Unauthorized user should be forbidden
    $this->actingAs($this->unauthorizedUser)
        ->post(route('activities.documents.upload', $this->activity), [
            'file' => $file,
            'description' => 'Test description',
        ])
        ->assertStatus(403);

    // Authorized user should be allowed
    $this->actingAs($this->authorizedUser)
        ->post(route('activities.documents.upload', $this->activity), [
            'file' => $file,
            'description' => 'Test description',
        ])
        ->assertRedirect();
});

it('restricts document deletion to owner/responsible/admin', function () {
    Storage::fake('public');
    $doc = ActivityDocument::create([
        'activity_id' => $this->activity->id,
        'uploaded_by' => $this->authorizedUser->id,
        'file_name' => 'test.pdf',
        'file_path' => 'activity-documents/test.pdf',
        'file_type' => 'pdf',
        'file_size' => 100,
        'version' => 1,
    ]);

    // Unauthorized user should be forbidden
    $this->actingAs($this->unauthorizedUser)
        ->delete(route('activities.documents.delete', $doc))
        ->assertStatus(403);

    // Authorized user (responsible user / same unit) should be allowed
    $this->actingAs($this->authorizedUser)
        ->delete(route('activities.documents.delete', $doc))
        ->assertRedirect();
});

it('restricts notifications marking as read to target user', function () {
    $notification = Notification::create([
        'user_id' => $this->authorizedUser->id,
        'title' => 'Test Notification',
        'message' => 'Test message',
        'type' => 'info',
    ]);

    // Unauthorized user should be forbidden
    $this->actingAs($this->unauthorizedUser)
        ->post(route('notifications.read', $notification))
        ->assertStatus(403);

    // Target user should be allowed
    $this->actingAs($this->authorizedUser)
        ->post(route('notifications.read', $notification))
        ->assertRedirect();
});
