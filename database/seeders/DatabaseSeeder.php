<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([

            CampusSeeder::class,

            ClaimTypeSeeder::class,

            PaymentMethodSeeder::class,

            SystemSettingSeeder::class,

            RolePermissionSeeder::class,

            AdminUserSeeder::class,

        ]);
    }
}