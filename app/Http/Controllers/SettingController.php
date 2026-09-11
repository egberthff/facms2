<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\SystemSetting;
use App\Services\CycleService;
use App\Services\TreasuryLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function __construct(
        private CycleService $cycleService,
        private TreasuryLedgerService $treasuryLedgerService,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $isPresident = Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']);
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
        if ($selectedCampusId) {
            $campus = $allCampuses->firstWhere('id', $selectedCampusId);
            if ($campus) {
                $cyclesByCampus->put($campus->id, [
                    'campus' => $campus,
                    'cycles' => $this->cycleService->list($campus->id),
                ]);
            }
        } elseif ($isPresident) {
            $cyclesByCampus = $allCampuses->mapWithKeys(fn ($campus) => [
                $campus->id => [
                    'campus' => $campus,
                    'cycles' => $this->cycleService->list($campus->id),
                ],
            ]);
        }

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
        $request->validate([
            'setting_value' => $setting->setting_key === 'claim_cycle_years'
                ? ['required', 'integer', 'between:1,20']
                : ($setting->setting_key === 'claim_cycle_start_month'
                    ? ['required', 'integer', 'between:1,12']
                    : ($setting->setting_key === 'association_fund'
                        ? ['required', 'numeric', 'decimal:0,2', 'gt:0']
                        : ['required', 'string'])),
            'campus_filter' => ['nullable', 'string'],
        ]);
    
        $user = Auth::user();

        $hasFullAccess = $user->can('view all campus data') 
            || $user->hasAnyRole(['Federated President', 'Federated Treasurer']);
        
        abort_if(!$hasFullAccess && $user->getCachedCampusId() !== $setting->campus_id, 403);

    
        DB::transaction(function () use ($request, $setting) {
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

            $currentCycleLabel = $this->cycleService
                ->currentRecord($setting->campus_id)
                ->cycle_label;

            SystemSetting::updateOrCreate(
                [
                    'campus_id' => $setting->campus_id,
                    'setting_key' => $setting->setting_key,
                    'cycle_label' => $currentCycleLabel,
                ],
                [
                    'setting_value' => $value,
                    'description' => "Historical snapshot of {$setting->setting_key} for cycle {$currentCycleLabel}",
                ]
            );

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

            if (in_array($setting->setting_key, ['claim_cycle_years', 'claim_cycle_start_month'], true)) {
                $this->cycleService->applyConfiguration($setting->campus_id);
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
