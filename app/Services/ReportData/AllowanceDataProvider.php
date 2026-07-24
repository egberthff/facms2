<?php

namespace App\Services\ReportData;

class AllowanceDataProvider
{
    /**
     * Mock implementation.
     *
     * Replace with database queries later.
     */
    public function getMemberBalances(array $filters = []): array
    {
        return [

            [

                'member_id' => 1,

                'member' => 'Juan Dela Cruz',

                'campus' => 'Main Campus',

                'allowance' => 25000,

                'approved' => 18000,

                'disbursed' => 15000,

                'remaining' => 7000,

                'cycle' => '2025-2027',

            ],

            [

                'member_id' => 2,

                'member' => 'Maria Santos',

                'campus' => 'North Campus',

                'allowance' => 25000,

                'approved' => 22000,

                'disbursed' => 22000,

                'remaining' => 3000,

                'cycle' => '2025-2027',

            ],

            [

                'member_id' => 3,

                'member' => 'Pedro Reyes',

                'campus' => 'South Campus',

                'allowance' => 25000,

                'approved' => 12000,

                'disbursed' => 9000,

                'remaining' => 13000,

                'cycle' => '2025-2027',

            ],

        ];
    }

}