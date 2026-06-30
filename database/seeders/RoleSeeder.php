<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super-admin',
                'display_name' => 'Super Admin',
                'description' => 'Administrator dengan akses penuh ke seluruh sistem',
                'level' => 0,
            ],
            [
                'name' => 'direktur',
                'display_name' => 'Direktur',
                'description' => 'Direktur Politeknik Pelayaran Barombong',
                'level' => 1,
            ],
            [
                'name' => 'wakil-direktur',
                'display_name' => 'Wakil Direktur',
                'description' => 'Wakil Direktur Politeknik Pelayaran Barombong',
                'level' => 2,
            ],
            [
                'name' => 'kepala-bagian',
                'display_name' => 'Kepala Bagian',
                'description' => 'Kepala Bagian/Unit Kerja',
                'level' => 3,
            ],
            [
                'name' => 'staf-keuangan',
                'display_name' => 'Staf Keuangan',
                'description' => 'Staf pengelola keuangan dan anggaran',
                'level' => 4,
            ],
            [
                'name' => 'staf-perencanaan',
                'display_name' => 'Staf Perencanaan',
                'description' => 'Staf perencanaan program dan kegiatan',
                'level' => 4,
            ],
            [
                'name' => 'operator-unit',
                'display_name' => 'Operator Unit',
                'description' => 'Operator unit kerja untuk input data kegiatan',
                'level' => 5,
            ],
            [
                'name' => 'auditor',
                'display_name' => 'Auditor',
                'description' => 'Auditor internal untuk pengawasan',
                'level' => 3,
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::create($roleData);
            $this->assignPermissions($role);
        }
    }

    private function assignPermissions(Role $role): void
    {
        $modules = [
            'dashboard', 'units', 'fiscal-years', 'renstras', 'renjas',
            'programs', 'activities', 'budgets', 'indicators', 'approvals',
            'users', 'audit-logs', 'reports', 'documents', 'notifications',
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'approve', 'export'];

        $permissionMap = match ($role->name) {
            'super-admin' => $this->allPermissions($modules, $actions),
            'direktur' => $this->directorPermissions($modules),
            'wakil-direktur' => $this->deputyDirectorPermissions($modules),
            'kepala-bagian' => $this->headOfDepartmentPermissions($modules),
            'staf-keuangan' => $this->financeStaffPermissions(),
            'staf-perencanaan' => $this->planningStaffPermissions(),
            'operator-unit' => $this->operatorPermissions(),
            'auditor' => $this->auditorPermissions($modules),
            default => [],
        };

        foreach ($permissionMap as $permission) {
            Permission::create([
                'role_id' => $role->id,
                'module' => $permission['module'],
                'action' => $permission['action'],
            ]);
        }
    }

    /**
     * @param  array<int, string>  $modules
     * @param  array<int, string>  $actions
     * @return array<int, array{module: string, action: string}>
     */
    private function allPermissions(array $modules, array $actions): array
    {
        $permissions = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions[] = ['module' => $module, 'action' => $action];
            }
        }

        return $permissions;
    }

    /**
     * @param  array<int, string>  $modules
     * @return array<int, array{module: string, action: string}>
     */
    private function directorPermissions(array $modules): array
    {
        $permissions = [];
        foreach ($modules as $module) {
            $permissions[] = ['module' => $module, 'action' => 'view'];
            if (in_array($module, ['approvals', 'renstras', 'programs'])) {
                $permissions[] = ['module' => $module, 'action' => 'approve'];
            }
        }
        $permissions[] = ['module' => 'reports', 'action' => 'export'];

        return $permissions;
    }

    /**
     * @param  array<int, string>  $modules
     * @return array<int, array{module: string, action: string}>
     */
    private function deputyDirectorPermissions(array $modules): array
    {
        $permissions = [];
        foreach ($modules as $module) {
            $permissions[] = ['module' => $module, 'action' => 'view'];
            if (in_array($module, ['approvals', 'programs', 'activities'])) {
                $permissions[] = ['module' => $module, 'action' => 'approve'];
            }
        }
        $permissions[] = ['module' => 'reports', 'action' => 'export'];

        return $permissions;
    }

    /**
     * @param  array<int, string>  $modules
     * @return array<int, array{module: string, action: string}>
     */
    private function headOfDepartmentPermissions(array $modules): array
    {
        $permissions = [];
        $editableModules = ['programs', 'activities', 'budgets', 'indicators', 'documents'];
        foreach ($modules as $module) {
            $permissions[] = ['module' => $module, 'action' => 'view'];
            if (in_array($module, $editableModules)) {
                $permissions[] = ['module' => $module, 'action' => 'create'];
                $permissions[] = ['module' => $module, 'action' => 'edit'];
            }
        }
        $permissions[] = ['module' => 'approvals', 'action' => 'approve'];
        $permissions[] = ['module' => 'reports', 'action' => 'export'];

        return $permissions;
    }

    /**
     * @return array<int, array{module: string, action: string}>
     */
    private function financeStaffPermissions(): array
    {
        $viewModules = ['dashboard', 'programs', 'activities', 'budgets', 'reports', 'notifications'];
        $permissions = [];
        foreach ($viewModules as $module) {
            $permissions[] = ['module' => $module, 'action' => 'view'];
        }
        foreach (['budgets'] as $module) {
            $permissions[] = ['module' => $module, 'action' => 'create'];
            $permissions[] = ['module' => $module, 'action' => 'edit'];
        }
        $permissions[] = ['module' => 'reports', 'action' => 'export'];

        return $permissions;
    }

    /**
     * @return array<int, array{module: string, action: string}>
     */
    private function planningStaffPermissions(): array
    {
        $viewModules = ['dashboard', 'renstras', 'renjas', 'programs', 'activities', 'budgets', 'indicators', 'reports', 'notifications'];
        $permissions = [];
        foreach ($viewModules as $module) {
            $permissions[] = ['module' => $module, 'action' => 'view'];
        }
        foreach (['renstras', 'renjas', 'programs', 'activities', 'indicators'] as $module) {
            $permissions[] = ['module' => $module, 'action' => 'create'];
            $permissions[] = ['module' => $module, 'action' => 'edit'];
        }
        $permissions[] = ['module' => 'reports', 'action' => 'export'];

        return $permissions;
    }

    /**
     * @return array<int, array{module: string, action: string}>
     */
    private function operatorPermissions(): array
    {
        $viewModules = ['dashboard', 'programs', 'activities', 'budgets', 'documents', 'notifications'];
        $permissions = [];
        foreach ($viewModules as $module) {
            $permissions[] = ['module' => $module, 'action' => 'view'];
        }
        foreach (['activities', 'documents'] as $module) {
            $permissions[] = ['module' => $module, 'action' => 'create'];
            $permissions[] = ['module' => $module, 'action' => 'edit'];
        }

        return $permissions;
    }

    /**
     * @param  array<int, string>  $modules
     * @return array<int, array{module: string, action: string}>
     */
    private function auditorPermissions(array $modules): array
    {
        $permissions = [];
        foreach ($modules as $module) {
            $permissions[] = ['module' => $module, 'action' => 'view'];
        }
        $permissions[] = ['module' => 'reports', 'action' => 'export'];
        $permissions[] = ['module' => 'audit-logs', 'action' => 'export'];

        return $permissions;
    }
}
