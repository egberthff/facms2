<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['GCash', 'Check'] as $method) {

            PaymentMethod::updateOrCreate(
                ['name' => $method],
                [
                    'is_active' => true
                ]
            );
        }
    }
}