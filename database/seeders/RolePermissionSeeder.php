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
            'view own contribution',
            'view all contribution',
            'update disbursement',
            'release payments',
            'manage members',
            'manage funds',
            'view member metrics',
            'view all metrics',
            'view all campus data', //reserve permission to federated roles
            'view all treasury',
            'view campus treasury',
            'view ledger',
            'view reports',
            'manage users',
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
            'view member metrics',
            'view own contribution'
        ]);

        // Treasurers manage funds and release payments - campus treasurer
        $treasurerRole = Role::findOrCreate('Treasurer', 'web');
        $treasurerRole->syncPermissions([
            'create claims',
            'view own claims',
            'release payments',
            'manage funds',
            'update claims',
            'view member metrics',
            'view all metrics',
            'view all claims',
            'view own contribution',
            'view campus treasury',
            'view ledger',
            'view campus treasury',
            'view reports'

        ]);

        $federatedTreasurerRole = Role::findOrCreate('Federated Treasurer', 'web');
        $federatedTreasurerRole->syncPermissions([
            'create claims', 
            'view own claims',
            'release payments',
            'manage funds',
            'update claims',
            'view member metrics',
            'view all metrics',
            'view all claims',
            'view own contribution',
            'view campus treasury',
            'view ledger',
            'view campus treasury',
            'view reports',
            'view all campus data'
        ]);

        //Presudent can do everythin on its scoped campus - campus president
        $presidentRole = Role::findOrCreate('President', 'web');
        $presidentRole->syncPermissions([
            'create claims', 
            'view own claims',
            'view all claims',
            'create claims',
            'update claims',
            'review claims',
            'view disbursment',
            'view own contribution',
            'view all contribution',
            'update disbursement',
            'release payments',
            'manage members',
            'manage funds',
            'view member metrics',
            'view all metrics',
            'view all treasury',
            'view campus treasury',
            'view ledger',
            'view reports',
            'manage users',
        ]);

        // Presidents can do everything - Federated President
        $federatedPresidentRole = Role::findOrCreate('Federated President', 'web');
        $federatedPresidentRole->syncPermissions(Permission::all());
    }


}
