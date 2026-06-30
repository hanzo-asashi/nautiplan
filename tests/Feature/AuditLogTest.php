<?php

use App\Models\AuditLog;
use App\Models\Role;
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
