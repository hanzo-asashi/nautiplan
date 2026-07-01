<?php

use App\Models\Activity;
use App\Models\ActivityDocument;
use App\Models\FiscalYear;
use App\Models\Notification;
use App\Models\Program;
use App\Models\SubActivity;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        'priority' => 'medium',
    ]);

    $this->subActivity = SubActivity::create([
        'activity_id' => $this->activity->id,
        'name' => 'Sub Activity Test',
        'status' => 'pending',
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-05',
        'progress_percentage' => 0,
        'assigned_to' => $this->user->id,
    ]);
});

it('can access kanban board page', function () {
    $response = $this->actingAs($this->user)->get(route('activities.kanban', $this->activity));
    $response->assertStatus(200);
});

it('can update sub activity status and progress and recalculates parent progress', function () {
    $response = $this->actingAs($this->user)->put(route('sub-activities.update-status', $this->subActivity), [
        'status' => 'in_progress',
        'progress_percentage' => 50,
    ]);

    $response->assertRedirect();
    $this->subActivity->refresh();
    expect($this->subActivity->status)->toBe('in_progress');
    expect($this->subActivity->progress_percentage)->toBe(50);

    // Recalculates parent
    $this->activity->refresh();
    expect($this->activity->progress_percentage)->toBe(50);

    // Check notification created
    $notification = Notification::where('user_id', $this->user->id)->first();
    expect($notification)->not->toBeNull();
    expect($notification->title)->toContain('Sub-Kegiatan Diperbarui');
});

it('can access calendar report page', function () {
    $response = $this->actingAs($this->user)->get(route('reports.calendar'));
    $response->assertStatus(200);
});

it('can access notifications list and mark notifications as read', function () {
    $notification = Notification::create([
        'user_id' => $this->user->id,
        'title' => 'Test Notification',
        'message' => 'Notification content text',
        'type' => 'info',
    ]);

    $response = $this->actingAs($this->user)->get(route('notifications.index'));
    $response->assertStatus(200);

    $readResponse = $this->actingAs($this->user)->post(route('notifications.read', $notification));
    $readResponse->assertRedirect();

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
});

it('can mark all notifications as read', function () {
    Notification::create([
        'user_id' => $this->user->id,
        'title' => 'Test Notification 1',
        'message' => 'Notification content text 1',
        'type' => 'info',
    ]);

    Notification::create([
        'user_id' => $this->user->id,
        'title' => 'Test Notification 2',
        'message' => 'Notification content text 2',
        'type' => 'info',
    ]);

    $readAllResponse = $this->actingAs($this->user)->post(route('notifications.read-all'));
    $readAllResponse->assertRedirect();

    $unreadCount = Notification::where('user_id', $this->user->id)->whereNull('read_at')->count();
    expect($unreadCount)->toBe(0);
});

it('automatically handles parent linking and version count on document upload collision', function () {
    Storage::fake('public');

    $file1 = UploadedFile::fake()->create('document_test.pdf', 100);
    $this->actingAs($this->user)->post(route('activities.documents.upload', $this->activity), [
        'file' => $file1,
        'description' => 'First upload version 1',
    ]);

    $rootDoc = ActivityDocument::where('activity_id', $this->activity->id)->whereNull('parent_id')->first();
    expect($rootDoc)->not->toBeNull();
    expect($rootDoc->version)->toBe(1);

    // Upload same file name again
    $file2 = UploadedFile::fake()->create('document_test.pdf', 150);
    $this->actingAs($this->user)->post(route('activities.documents.upload', $this->activity), [
        'file' => $file2,
        'description' => 'Second upload version 2',
    ]);

    $newDoc = ActivityDocument::where('activity_id', $this->activity->id)->whereNotNull('parent_id')->first();
    expect($newDoc)->not->toBeNull();
    expect($newDoc->parent_id)->toBe($rootDoc->id);
    expect($newDoc->version)->toBe(2);
});

it('can access revisions page', function () {
    $response = $this->actingAs($this->user)->get(route('activities.revisions', $this->activity));
    $response->assertStatus(200);
});
