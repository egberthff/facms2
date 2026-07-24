<?php

namespace App\Services\Reports;

use App\Services\Reports\Contracts\ReportInterface;

abstract class BaseReport implements ReportInterface
{
    /**
     * Active filters.
     */
    protected array $filters = [];

    /**
     * Report metadata.
     */
    protected array $meta = [];

    /**
     * Report context.
     */
    protected array $context = [];

    /**
     * KPI Cards.
     */
    protected array $cards = [];

    /**
     * Table columns.
     */
    protected array $columns = [];

    /**
     * Table rows.
     */
    protected array $rows = [];

    /**
     * Generate report.
     */
    public function generate(array $filters = []): array
    {
        $this->filters = $filters;

        $this->build();

        return [

            'meta' => $this->meta(),

            'context' => $this->context(),

            'cards' => $this->cards(),

            'columns' => $this->columns(),

            'rows' => $this->rows(),

        ];
    }

    /**
     * Build report.
     */
    abstract protected function build(): void;

    /**
     * Metadata.
     */
    protected function meta(): array
    {
        return array_merge([

            'generated_at' => now()->format('F d, Y h:i A'),

            'record_count' => count($this->rows),

            'exportable' => true,

            'printable' => true,

            'empty_message' => 'No records found.'

        ], $this->meta);
    }

    /**
     * Context.
     */
    protected function context(): array
    {
        return $this->context;
    }

    /**
     * KPI Cards.
     */
    protected function cards(): array
    {
        return $this->cards;
    }

    /**
     * Columns.
     */
    protected function columns(): array
    {
        return $this->columns;
    }

    /**
     * Rows.
     */
    protected function rows(): array
    {
        return $this->rows;
    }

    /**
     * Helper for summary cards.
     */
    protected function card(
        string $key,
        string $label,
        mixed $value,
        ?string $subtitle = null
    ): array {

        return [

            'key' => $key,

            'label' => $label,

            'value' => $value,

            'subtitle' => $subtitle,

        ];

    }

    /**
     * Helper for table columns.
     */
    protected function column(
        string $key,
        string $label,
        string $type = 'text'
    ): array {

        return [

            'key' => $key,

            'label' => $label,

            'type' => $type,

        ];

    }
}