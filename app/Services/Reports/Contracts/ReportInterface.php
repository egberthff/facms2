<?php

namespace App\Services\Reports\Contracts;

interface ReportInterface
{
    /**
     * Generate the report.
     *
     * @param array $filters
     * @return array
     */
    public function generate(array $filters = []): array;
}