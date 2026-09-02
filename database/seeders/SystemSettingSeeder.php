<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f6865-edd5-730f-accc-33e9fab1a422', //BISU - Calape
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a', //BISU - Main
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f686b-6d17-713d-974e-eb7fca91c245', //BISU -Balilihan
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f686b-6d1a-7174-a19b-aca9360c5b6a', //BISU - Bilar
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f686b-6d1d-73bc-8e32-f9ab28f8e451', //BISU - Candijay
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f686b-6d20-711c-a538-433cb3f7b4f5', //BISU - Clarin
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f6865-edd5-730f-accc-33e9fab1a422', //Bisu - Calape
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a', //Bisu - Main
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d17-713d-974e-eb7fca91c245', //Bisu - Balilihan
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d1a-7174-a19b-aca9360c5b6a', //Bisu - Bilar
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d1d-73bc-8e32-f9ab28f8e451', //Bisu - Candijay
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d20-711c-a538-433cb3f7b4f5', //Bisu - Clarin
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f6865-edd5-730f-accc-33e9fab1a422', //Bisu -Calape
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a', //Bisu - Main
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d17-713d-974e-eb7fca91c245', //Bisu - Balilihan
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d1a-7174-a19b-aca9360c5b6a', //Bisu - Bilar
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d1d-73bc-8e32-f9ab28f8e451', //Bisu - Candijay
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d20-711c-a538-433cb3f7b4f5', //Bisu - Clarin
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current annual contribution minimum ammount.',
                'campus_id' => '019f6865-edd5-730f-accc-33e9fab1a422', //Bisu - Calape
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current annual contribution minimum ammount.',
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a', //Bisu - Main
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current annual contribution minimum ammount.',
                'campus_id' => '019f686b-6d17-713d-974e-eb7fca91c245', //Bisu - Balilihan
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current annual contribution minimum ammount.',
                'campus_id' => '019f686b-6d1a-7174-a19b-aca9360c5b6a', //Bisu - Bilar
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current annual contribution minimum ammount.',
                'campus_id' => '019f686b-6d1d-73bc-8e32-f9ab28f8e451', //Bisu - Candijay
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current annual contribution minimum ammount.',
                'campus_id' => '019f686b-6d20-711c-a538-433cb3f7b4f5', //Bisu - Clarin
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current annual contribution minimum ammount.',
                'campus_id' => '019f686b-6d20-711c-b538-433cb3f7b789', //All campus
            ],
        ];

        $campusIds = collect($settings)
            ->pluck('campus_id')
            ->unique();

        foreach ($campusIds as $campusId) {
            $settings[] = [
                'setting_key' => 'claim_cycle_years',
                'setting_value' => '2',
                'description' => 'Number of years in the claims cycle.',
                'campus_id' => $campusId,
            ];
            $settings[] = [
                'setting_key' => 'claim_cycle_start_month',
                'setting_value' => '10',
                'description' => 'Month when the claims cycle starts (1-12).',
                'campus_id' => $campusId,
            ];
        }

        foreach ($settings as $setting) {

            SystemSetting::updateOrCreate(
                [
                    'setting_key' => $setting['setting_key'],
                    'campus_id'   => $setting['campus_id']
                ], 
                $setting
            );
        }
    }
}