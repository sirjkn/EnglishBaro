<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-courses' => 'Manage Courses',
            'manage-students' => 'Manage Students',
            'manage-payments' => 'Manage Payments',
            'manage-settings' => 'Manage Settings',
            'manage-assessments' => 'Manage Assessments',
            'view-audit-logs' => 'View Audit Logs',
        ];

        foreach ($permissions as $slug => $name) {
            Permission::query()->updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $adminRole = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Full administrative access']
        );

        $adminRole->permissions()->sync(Permission::query()->pluck('id'));

        Role::query()->updateOrCreate(
            ['slug' => 'student'],
            ['name' => 'Student', 'description' => 'Standard student access']
        );
    }
}
