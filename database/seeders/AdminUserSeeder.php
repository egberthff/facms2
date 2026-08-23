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
            ['email' => 'president@bisu.edu.ph'],
            [
                'email' => 'president@bisu.edu.ph',
                'name' => 'System President',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $user->assignRole('Federated President');

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
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a',
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'member@bisu.edu.ph'],
            [
                'email' => 'member@bisu.edu.ph',
                'name' => 'Member Test Account',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $user->assignRole('Member');

        Member::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'employee_number' => 'EMP-0002',
                'first_name' => 'Member',
                'middle_name' => 'Test',
                'last_name' => 'Account',
                'suffix' => null,
                'gender' => 'Male',
                'birth_date' => '1990-01-01',
                'department' => 'CCJ',
                'position' => 'Member',
                'employment_status' => 'Permanent',
                'membership_status' => 'Active',
                'contact_number' => '09123456790',
                'address' => 'N/A',
                'gcash_number' => null,
                'gcash_account_name' => null,
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a',
            ]
        );
    }
}