<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([

            ClaimTypeSeeder::class,

            PaymentMethodSeeder::class,

            SystemSettingSeeder::class,

            RolePermissionSeeder::class,

            AdminUserSeeder::class,

        ]);
    }
}