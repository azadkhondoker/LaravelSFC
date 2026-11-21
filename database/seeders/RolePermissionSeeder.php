<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            'view members',
            'create members',
            'edit members',
            'delete members',
        ])->map(fn (string $name) => Permission::findOrCreate($name, 'web'));

        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->syncPermissions($permissions);
    }
}
