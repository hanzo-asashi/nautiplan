<?php

use App\Models\Activity;
use App\Models\ActivityBudget;
use App\Models\AuditLog;
use App\Models\BudgetItem;
use App\Models\BudgetRealization;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;

test('guests are redirected to the login page from audit logs', function () {
    $response = $this->get(route('audit-logs.index'));
    $response->assertRedirect(route('login'));
});

test('non-admin authenticated users cannot visit the audit logs page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('audit-logs.index'));
    $response->assertStatus(403);
});

test('super-admin authenticated users can visit the audit logs page', function () {
    // Create super-admin role
    $role = Role::create([
        'name' => 'super-admin',
        'display_name' => 'Super Admin',
        'level' => 0,
    ]);

    $user = User::factory()->create();
    $user->roles()->attach($role);

    $this->actingAs($user);

    $response = $this->get(route('audit-logs.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('audit-logs/Index')
        ->has('logs')
        ->has('users')
        ->has('filters')
    );
});

test('super-admin can filter audit logs by search query, user, and event', function () {
    // Create super-admin role
    $role = Role::create([
        'name' => 'super-admin',
        'display_name' => 'Super Admin',
        'level' => 0,
    ]);

    $admin = User::factory()->create(['name' => 'Admin User']);
    $admin->roles()->attach($role);

    $otherUser = User::factory()->create(['name' => 'Other User']);

    // Create some audit logs
    AuditLog::create([
        'user_id' => $admin->id,
        'auditable_type' => 'App\Models\Renstra',
        'auditable_id' => 1,
        'event' => 'created',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla',
    ]);

    AuditLog::create([
        'user_id' => $otherUser->id,
        'auditable_type' => 'App\Models\Activity',
        'auditable_id' => 2,
        'event' => 'updated',
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Chrome',
    ]);

    $this->actingAs($admin);

    // 1. Test filtering by search query (e.g. IP address)
    $response = $this->get(route('audit-logs.index', ['search' => '192.168.1.1']));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('logs.data.0.ip_address', '192.168.1.1')
        ->has('logs.data', 1)
    );

    // 2. Test filtering by user
    $response = $this->get(route('audit-logs.index', ['user_id' => $otherUser->id]));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('logs.data.0.user_id', $otherUser->id)
        ->has('logs.data', 1)
    );

    // 3. Test filtering by event
    $response = $this->get(route('audit-logs.index', ['event' => 'created']));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('logs.data.0.event', 'created')
        ->has('logs.data', 1)
    );
});

test('model operations automatically write audit trail log entries', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $fiscalYear = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
        'is_locked' => false,
    ]);

    $unit = Unit::create([
        'code' => 'UNIT-01',
        'name' => 'Unit Kerja',
    ]);

    $program = Program::create([
        'code' => 'PRG-01',
        'name' => 'Program Pengembangan',
        'fiscal_year_id' => $fiscalYear->id,
        'unit_id' => $unit->id,
        'created_by' => $user->id,
    ]);

    $activity = Activity::create([
        'code' => 'AUDIT-TEST',
        'name' => 'Testing Audit Log Integration',
        'program_id' => $program->id,
        'unit_id' => $unit->id,
        'fiscal_year_id' => $fiscalYear->id,
        'status' => 'draft',
    ]);

    // 1. Create ActivityBudget
    $budget = ActivityBudget::create([
        'activity_id' => $activity->id,
        'budget_category' => 'goods_services',
        'account_code' => '521211',
        'account_name' => 'Belanja Bahan',
        'description' => 'ATK Uji Coba',
        'amount' => 500000.0,
        'fiscal_year_id' => $fiscalYear->id,
        'version' => 1,
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'auditable_type' => 'App\Models\ActivityBudget',
        'auditable_id' => $budget->id,
        'event' => 'created',
    ]);

    // 2. Create BudgetItem
    $item = BudgetItem::create([
        'activity_budget_id' => $budget->id,
        'name' => 'Kertas A4',
        'volume' => 5,
        'unit' => 'Rim',
        'unit_price' => 100000.0,
        'total' => 500000.0,
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'auditable_type' => 'App\Models\BudgetItem',
        'auditable_id' => $item->id,
        'event' => 'created',
    ]);

    // 3. Update BudgetItem
    $item->update(['volume' => 10]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'auditable_type' => 'App\Models\BudgetItem',
        'auditable_id' => $item->id,
        'event' => 'updated',
    ]);

    // 4. Create BudgetRealization
    $realization = BudgetRealization::create([
        'activity_budget_id' => $budget->id,
        'realization_type' => 'non_pengadaan',
        'amount' => 200000.0,
        'realization_date' => '2026-07-01',
        'description' => 'Realisasi Uji Coba',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'auditable_type' => 'App\Models\BudgetRealization',
        'auditable_id' => $realization->id,
        'event' => 'created',
    ]);
});
