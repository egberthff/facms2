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
            'view own claims',
            'view all claims',
            'create claims',
            'update claims',
            'review claims',
            'view disbursment',
            'update disbursement',
            'release payments',
            'manage members',
            'manage funds',
            'view member metrics',
            'view all metrics'
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
            'view own claims',
            'view member metrics'
        ]);

        // Treasurers manage funds and release payments
        $treasurerRole = Role::findOrCreate('Treasurer', 'web');
        $treasurerRole->syncPermissions([
            'view own claims',
            'release payments',
            'manage funds',
            'update claims',
            'view member metrics',
            'view all metrics',
            'view all claims'
        ]);

        // Presidents can do everything
        $presidentRole = Role::findOrCreate('President', 'web');
        $presidentRole->syncPermissions(Permission::all());
    }
}
