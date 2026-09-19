<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\ClaimsCycle;
use App\Models\SystemSetting;
use App\Services\TreasuryLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    public function __construct(
        private TreasuryLedgerService $treasuryLedgerService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $isPresident = Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['President', 'Treasurer']);
        $selectedCampusId = $isPresident
            ? $request->input('campus_filter')
            : Auth::user()->getCachedCampusId();

        $allCampuses = Campus::where('is_active', true)->get();
        $settings = SystemSetting::query()
            ->when($selectedCampusId, fn($query) => $query->where('campus_id', $selectedCampusId))
            ->whereNull('cycle_label')
            ->orderBy('setting_key')
            ->orderBy('campus_id')
            ->get();

        $cyclesByCampus = collect();

        return view('settings.index', compact('settings', 'allCampuses', 'selectedCampusId', 'isPresident', 'cyclesByCampus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SystemSetting $setting)
    {
        $cycleSettingKeys = ['claim_cycle_start_year', 'claim_cycle_start_month', 'claim_cycle_years'];
        $isCycleConfiguration = $setting->setting_key === 'claim_cycle_start_year'
            && $request->has(['cycle_start_year', 'cycle_start_month', 'cycle_years']);

        if ($isCycleConfiguration) {
            $request->validate([
                'cycle_start_year' => ['required', 'integer', 'between:1900,2200'],
                'cycle_start_month' => ['required', 'integer', 'between:1,12'],
                'cycle_years' => ['required', 'integer', 'between:1,20'],
                'campus_filter' => ['nullable', 'string'],
            ]);
        }

        $validationRules = match ($setting->setting_key) {
            'claim_cycle_start_year' => ['required', 'integer', 'between:1900,2200'],
            'claim_cycle_start_month' => ['required', 'integer', 'between:1,12'],
            'claim_cycle_years' => ['required', 'integer', 'between:1,20'],
            'association_fund' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            default => ['required', 'string'],
        };

        if (!$isCycleConfiguration) {
            $request->validate([
                'setting_value' => $validationRules,
                'campus_filter' => ['nullable', 'string'],
            ]);
        }

        $user = Auth::user();

        $hasFullAccess = $user->can('view all campus data') 
            || $user->hasAnyRole(['President', 'Treasurer']);
        
        abort_if(!$hasFullAccess && $user->getCachedCampusId() !== $setting->campus_id, 403);

        // if (in_array($setting->setting_key, $cycleSettingKeys, true)
        //     && ClaimsCycle::where('campus_id', $setting->campus_id)->exists()) {
        //     throw ValidationException::withMessages([
        //         'setting_value' => 'Claim cycle settings cannot be changed after a cycle has been initialized.',
        //     ]);
        // }

        if (in_array($setting->setting_key, $cycleSettingKeys, true)) {
            
            // 1. Extract values from either the bulk request payload or the single-key payload
            if ($isCycleConfiguration) {
                $startYear = (int) $request->input('cycle_start_year');
                $duration  = (int) $request->input('cycle_years');
            } else {
                // If modifying a single key, fallback to database values for the missing parts
                $startYear = $setting->setting_key === 'claim_cycle_start_year' 
                    ? (int) $request->input('setting_value') 
                    : (int) SystemSetting::where('campus_id', $setting->campus_id)->where('setting_key', 'claim_cycle_start_year')->value('setting_value');
                    
                $duration = $setting->setting_key === 'claim_cycle_years' 
                    ? (int) $request->input('setting_value') 
                    : (int) SystemSetting::where('campus_id', $setting->campus_id)->where('setting_key', 'claim_cycle_years')->value('setting_value');
            }

            if ($startYear && $duration) {
                $endYear = $startYear + $duration;
                $targetLabel = "{$startYear}-{$endYear}";

                // 2. Map structural timestamp (October 1st) matching your generation script
                $targetStart = now()->setDate($startYear, 10, 1)->startOfDay();

                // 3. See if this timestamp collides with any already initialized cycles
                $timelineOverlaps = \App\Models\ClaimsCycle::where('campus_id', $setting->campus_id)
                    ->where('start_date', '<=', $targetStart)
                    ->where('end_date', '>=', $targetStart)
                    ->exists();

                if ($timelineOverlaps) {
                    $errorField = $isCycleConfiguration ? 'cycle_start_year' : 'setting_value';
                    throw ValidationException::withMessages([
                        $errorField => "The timeline or cycle label '{$targetLabel}' has already been initialized and cannot be overridden.",
                    ]);
                }
            }
        }


        DB::transaction(function () use ($request, $setting, $isCycleConfiguration) {
            if ($isCycleConfiguration) {
                foreach ([
                    'claim_cycle_start_year' => $request->input('cycle_start_year'),
                    'claim_cycle_start_month' => $request->input('cycle_start_month'),
                    'claim_cycle_years' => $request->input('cycle_years'),
                ] as $settingKey => $value) {
                    $liveSetting = SystemSetting::query()
                        ->where('setting_key', $settingKey)
                        ->where('campus_id', $setting->campus_id)
                        ->whereNull('cycle_label')
                        ->lockForUpdate()
                        ->firstOrFail();

                    $liveSetting->update(['setting_value' => $value]);
                }

                return;
            }

            $liveSetting = SystemSetting::query()
                ->where('setting_key', $setting->setting_key)
                ->where('campus_id', $setting->campus_id)
                ->whereNull('cycle_label')
                ->lockForUpdate()
                ->firstOrFail();

            $value = $request->input('setting_value');
            if ($setting->setting_key === 'association_fund') {
                $value = number_format((float) $liveSetting->setting_value + (float) $value, 2, '.', '');
            }

            $currentCycle = ClaimsCycle::query()
                ->where('campus_id', $setting->campus_id)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if ($currentCycle) {
                SystemSetting::updateOrCreate(
                    [
                        'campus_id' => $setting->campus_id,
                        'setting_key' => $setting->setting_key,
                        'cycle_label' => $currentCycle->cycle_label,
                    ],
                    [
                        'setting_value' => $value,
                        'description' => "Historical snapshot of {$setting->setting_key} for cycle {$currentCycle->cycle_label}",
                    ]
                );
            }

            $liveSetting->update(['setting_value' => $value]);

            if ($setting->setting_key === 'association_fund') {
                $this->treasuryLedgerService->recordTransaction([
                    'campus_id' => $setting->campus_id,
                    'sourceable_type' => SystemSetting::class,
                    'sourceable_id' => $liveSetting->id,
                    'reference_number' => 'AF-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6)),
                    'description' => 'Association fund cash-in',
                    'type' => 'Credit',
                    'amount' => $request->input('setting_value'),
                ]);
            }

        });
    
        return redirect()
            ->route('settings.index', ['campus_filter' => $request->input('campus_filter')])
            ->with('success', 'System setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
