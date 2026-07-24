<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use InvalidArgumentException;

class ReportService
{
    /**
     * Generate a report.
     */
    public function generate(string $reportType, array $filters = []): array
    {
        $reportClass = config("reports.types.{$reportType}.handler");

        if (!$reportClass) {
            throw new InvalidArgumentException(
                "Unknown report type [{$reportType}]"
            );
        }

        return App::make($reportClass)->generate($filters);
    }

    /**
     * Export PDF.
     *
     * Placeholder.
     */
    public function exportPdf(string $reportType, array $filters = [])
    {
        $report = $this->generate($reportType, $filters);

        /*
        |--------------------------------------------------------------------------
        | TODO
        |--------------------------------------------------------------------------
        |
        | Return Barryvdh\DomPDF
        |
        */

        abort(501, 'PDF export not implemented.');
    }

    /**
     * Export Excel.
     *
     * Placeholder.
     */
    public function exportExcel(string $reportType, array $filters = [])
    {
        $report = $this->generate($reportType, $filters);

        /*
        |--------------------------------------------------------------------------
        | TODO
        |--------------------------------------------------------------------------
        |
        | Return Laravel Excel
        |
        */

        abort(501, 'Excel export not implemented.');
    }

    /**
     * Campus dropdown.
     *
     * Replace with Campus model later.
     */
    public function getCampuses(): array
    {
        return [

            [
                'id' => 1,
                'name' => 'Main Campus',
            ],

            [
                'id' => 2,
                'name' => 'North Campus',
            ],

            [
                'id' => 3,
                'name' => 'South Campus',
            ],

        ];
    }

    /**
     * Member dropdown.
     *
     * Replace with Member model later.
     */
    public function getMembers(): array
    {
        return [

            [

                'id' => 1,

                'name' => 'Juan Dela Cruz'

            ],

            [

                'id' => 2,

                'name' => 'Maria Santos'

            ],

            [

                'id' => 3,

                'name' => 'Pedro Reyes'

            ],

        ];
    }

    /**
     * Available Allowance Cycles.
     *
     * Eventually this can be generated
     * dynamically from System Settings.
     */
    public function getAllowanceCycles(): array
    {
        return [

            [

                'value' => 'current',

                'label' => 'Current Cycle'

            ],

            [

                'value' => '2023-2025',

                'label' => '2023 - 2025'

            ],

            [

                'value' => '2025-2027',

                'label' => '2025 - 2027'

            ],

        ];
    }
}