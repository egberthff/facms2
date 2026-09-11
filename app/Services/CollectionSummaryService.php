<?php

namespace App\Services;

use App\Models\CollectionSummaryItem;
use App\Models\CollectionSummaryPeriod;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class CollectionSummaryService
{
    public function store(array $data, User $user): CollectionSummaryPeriod
    {
        $summaryData = [
            'campus_id' => $data['campus_id'],
            'period_label' => $data['period_label'],
            'title' => $data['title'] ?? 'Collection Summary',
            'description' => $data['description'] ?? null,
            'prepared_by' => $user->user_id,
            'status' => $data['status'] ?? 'draft',
        ];

        return DB::transaction(function () use ($summaryData, $data) {
            $summary = CollectionSummaryPeriod::create($summaryData);

            $items = $data['items'] ?? [];

            foreach ($items as $index => $item) {
                CollectionSummaryItem::create([
                    'summary_id' => $summary->id,
                    'section' => $item['section'] ?? 'collections',
                    'label' => $item['label'] ?? 'Untitled item',
                    'amount' => (float) ($item['amount'] ?? 0),
                    'is_manual' => (bool) ($item['is_manual'] ?? true),
                    'sort_order' => $index,
                    'source_type' => $item['source_type'] ?? null,
                    'source_reference' => $item['source_reference'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $summary->load('items');
        });
    }

    public function latestForCampus(?string $campusId = null): ?CollectionSummaryPeriod
    {
        $query = CollectionSummaryPeriod::query()->with('items')->latest('created_at');

        if ($campusId) {
            $query->where('campus_id', $campusId);
        }

        return $query->first();
    }

    public function buildPrintableData(?string $campusId = null, ?string $periodLabel = null): array
    {
        $summary = $this->latestForCampus($campusId);

        if (!$summary) {
            return [
                'summary' => null,
                'collections' => [],
                'disbursements' => [],
                'collection_total' => 0.00,
                'disbursement_total' => 0.00,
            ];
        }

        $collections = $summary->items()->where('section', 'collections')->orderBy('sort_order')->get();
        $disbursements = $summary->items()->where('section', 'disbursements')->orderBy('sort_order')->get();

        return [
            'summary' => $summary,
            'collections' => $collections,
            'disbursements' => $disbursements,
            'collection_total' => (float) $collections->sum('amount'),
            'disbursement_total' => (float) $disbursements->sum('amount'),
        ];
    }
}
