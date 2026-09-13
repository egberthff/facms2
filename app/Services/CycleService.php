<?php

namespace App\Services;

use App\Models\ClaimsCycle;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CycleService
{
    public function current(?string $campusId = null, ?Carbon $date = null): array
    {
        $cycle = $this->currentRecord($campusId, $date);

        return [$cycle->start_date, $cycle->end_date, 'Current Cycle'];
    }

    public function currentRecord(?string $campusId = null, ?Carbon $date = null): ClaimsCycle
    {
        $date ??= now();
        $cycle = ClaimsCycle::query()
            ->where('campus_id', $campusId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$cycle) {
            $cycle = $this->ensureCurrentCycle($campusId, $date);
        }

        return $cycle;
    }

    public function resolve(?string $cycle, ?string $campusId = null, ?Carbon $date = null): array
    {
        if (empty($cycle) || $cycle === 'current') {
            return $this->current($campusId, $date);
        }

        $storedCycle = ClaimsCycle::query()
            ->where('campus_id', $campusId)
            ->where('cycle_label', trim($cycle))
            ->first();

        if ($storedCycle) {
            return [$storedCycle->start_date, $storedCycle->end_date, $storedCycle->cycle_label];
        }

        if (preg_match('/^\d{4}-\d{4}$/', trim($cycle))) {
            throw new \InvalidArgumentException("Claims cycle [{$cycle}] was not found for campus [{$campusId}].");
        }

        return $this->current($campusId, $date);
    }

    public function labelForStartYear(int $startYear, ?string $campusId = null): string
    {
        return $startYear . '-' . ($startYear + SystemSetting::getCycleYears($campusId));
    }

    public function list(?string $campusId = null, int $history = 3, ?Carbon $date = null): array
    {
        $date ??= now();
        $this->ensureCurrentCycle($campusId, $date);

        return ClaimsCycle::query()
            ->where('campus_id', $campusId)
            ->where('start_date', '<=', $date)
            ->orderByDesc('start_date')
            ->limit($history + 1)
            ->get()
            ->map(fn (ClaimsCycle $cycle) => [
                'label' => $cycle->cycle_label,
                'start' => $cycle->start_date,
                'end' => $cycle->end_date,
                'is_current' => $date->betweenIncluded($cycle->start_date, $cycle->end_date),
            ])
            ->all();
    }

    public function applyConfiguration(string $campusId): ClaimsCycle
    {
        return DB::transaction(function () use ($campusId) {
            $years = SystemSetting::getCycleYears($campusId);
            $startMonth = SystemSetting::getCycleStartMonth($campusId);
            $latest = ClaimsCycle::query()
                ->where('campus_id', $campusId)
                ->orderByDesc('start_date')
                ->lockForUpdate()
                ->first();

            if (!$latest) {
                return $this->ensureCurrentCycle($campusId);
            }

            $sameConfiguration = $latest->duration_years === $years
                && $latest->start_month === $startMonth;

            if ($sameConfiguration || $latest->end_date->isFuture()) {
                return $latest;
            }

            return $this->createNextCycle($latest, $years, $startMonth);
        });
    }

    private function ensureCurrentCycle(?string $campusId, ?Carbon $date = null): ClaimsCycle
    {
        if (!$campusId) {
            throw new \InvalidArgumentException('A campus is required to resolve a claims cycle.');
        }

        $date ??= now();
        $latest = ClaimsCycle::query()
            ->where('campus_id', $campusId)
            ->orderByDesc('start_date')
            ->first();

        if ($latest) {
            while ($latest->end_date->lt($date)) {
                $latest = $this->createNextCycle(
                    $latest,
                    SystemSetting::getCycleYears($campusId),
                    SystemSetting::getCycleStartMonth($campusId)
                );
            }

            return $latest;
        }

        $years = SystemSetting::getCycleYears($campusId);
        $startMonth = SystemSetting::getCycleStartMonth($campusId);
        $startYear = SystemSetting::getCycleStartYear($campusId);
        $start = Carbon::create($startYear, $startMonth, 1, 0, 0, 0);
        $end = (clone $start)->addYears($years)->subSecond();

        $cycle = ClaimsCycle::create([
            'campus_id' => $campusId,
            'cycle_label' => $startYear . '-' . ($startYear + $years),
            'start_date' => $start,
            'end_date' => $end,
            'duration_years' => $years,
            'start_month' => $startMonth,
        ]);

        while ($cycle->end_date->lt($date)) {
            $cycle = $this->createNextCycle($cycle, $years, $startMonth);
        }

        return $cycle;
    }

    private function createNextCycle(ClaimsCycle $previous, int $years, int $startMonth): ClaimsCycle
    {
        $start = $previous->end_date->copy()->addSecond();
        $end = $start->copy()->addYears($years)->subSecond();

        return ClaimsCycle::firstOrCreate(
            [
                'campus_id' => $previous->campus_id,
                'start_date' => $start,
            ],
            [
                'cycle_label' => $start->year . '-' . ($start->year + $years),
                'end_date' => $end,
                'duration_years' => $years,
                'start_month' => $startMonth,
            ]
        );
    }
}