<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'president@facms.test'],
            [
                'email' => 'president@bisu.edu.ph',
                'name' => 'System President',
                'password' => Hash::make('123456'),
                'is_active' => true,
            ]
        );

        $user->assignRole('President');

        Member::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'employee_number' => 'EMP-0001',
                'first_name' => 'System',
                'middle_name' => null,
                'last_name' => 'President',
                'suffix' => null,
                'gender' => 'Male',
                'birth_date' => '1990-01-01',
                'department' => 'Administration',
                'position' => 'President',
                'employment_status' => 'Permanent',
                'membership_status' => 'Active',
                'contact_number' => '09123456789',
                'address' => 'N/A',
                'gcash_number' => null,
                'gcash_account_name' => null,
            ]
        );
    }
}