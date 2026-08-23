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
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a',
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f6865-edd5-730f-accc-33e9fab1a422',
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f686b-6d17-713d-974e-eb7fca91c245',
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f686b-6d1a-7174-a19b-aca9360c5b6a',
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f686b-6d1d-73bc-8e32-f9ab28f8e451',
            ],

            [
                'setting_key' => 'annual_claim_limit',
                'setting_value' => '23000',
                'description' => 'Maximum annual benefit per member.',
                'campus_id' => '019f686b-6d20-711c-a538-433cb3f7b4f5',
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a',
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f6865-edd5-730f-accc-33e9fab1a422',
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d17-713d-974e-eb7fca91c245',
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d1a-7174-a19b-aca9360c5b6a',
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d1d-73bc-8e32-f9ab28f8e451',
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
                'campus_id' => '019f686b-6d20-711c-a538-433cb3f7b4f5',
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a',
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f6865-edd5-730f-accc-33e9fab1a422',
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d17-713d-974e-eb7fca91c245',
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d1a-7174-a19b-aca9360c5b6a',
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d1d-73bc-8e32-f9ab28f8e451',
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d20-711c-a538-433cb3f7b4f5',
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d0e-7027-98a0-c16ed47b318a',
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f6865-edd5-730f-accc-33e9fab1a422',
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d17-713d-974e-eb7fca91c245',
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d1a-7174-a19b-aca9360c5b6a',
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d1d-73bc-8e32-f9ab28f8e451',
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d20-711c-a538-433cb3f7b4f5',
            ],

            [
                'setting_key' => 'annual_contribution',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
                'campus_id' => '019f686b-6d20-711c-b538-433cb3f7b789',
            ],
        ];

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