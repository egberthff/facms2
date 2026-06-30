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
            ],

            [
                'setting_key' => 'member_contribution',
                'setting_value' => '50',
                'description' => 'Contribution per approved claim.',
            ],

            [
                'setting_key' => 'association_fund',
                'setting_value' => '0',
                'description' => 'Current association fund balance.',
            ],
        ];

        foreach ($settings as $setting) {

            SystemSetting::updateOrCreate(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
}