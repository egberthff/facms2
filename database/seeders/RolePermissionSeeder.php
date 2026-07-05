<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Define your application permissions
        $permissions = [
            'view claims',
            'create claims',
            'update claims',
            'review claims',
            'release payments',
            'manage members',
            'manage funds',
        ];

        // 3. Create permissions in the database
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 4. Create roles and sync permissions
        
        // Members can only make and view claims
        $memberRole = Role::findOrCreate('Member', 'web');
        $memberRole->syncPermissions([
            'create claims', 
            'view claims',
        ]);

        // Treasurers manage funds and release payments
        $treasurerRole = Role::findOrCreate('Treasurer', 'web');
        $treasurerRole->syncPermissions([
            'view claims',
            'release payments',
            'manage funds',
            'update claims',
        ]);

        // Presidents can do everything
        $presidentRole = Role::findOrCreate('President', 'web');
        $presidentRole->syncPermissions(Permission::all());
    }
}
