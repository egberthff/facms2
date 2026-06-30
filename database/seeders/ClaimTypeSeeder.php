<?php

namespace Database\Seeders;

use App\Models\ClaimType;
use Illuminate\Database\Seeder;

class ClaimTypeSeeder extends Seeder
{
    public function run(): void
    {
        $claimTypes = [
            [
                'name' => 'Outpatient',
                'required_document' => 'Medical Certificate',
                'description' => 'Outpatient medical consultation and treatment.',
                'is_active' => true,
            ],
            [
                'name' => 'Admission',
                'required_document' => 'Medical Certificate',
                'description' => 'Hospital admission claim.',
                'is_active' => true,
            ],
            [
                'name' => 'Burial',
                'required_document' => 'Death Certificate',
                'description' => 'Burial assistance claim.',
                'is_active' => true,
            ],
        ];

        foreach ($claimTypes as $claimType) {
            ClaimType::updateOrCreate(
                ['name' => $claimType['name']],
                $claimType
            );
        }
    }
}