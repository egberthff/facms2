<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\ClaimType;
use App\Models\ClaimsCycle;
use App\Models\Member;
use App\Models\SystemSetting;
use App\Services\TreasuryLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        $claimTypes = $isPresident ? ClaimType::orderBy('name')->get() : collect();

        // Committee on Claims slots store member ids; resolve them to members for display.
        $committeeSettings = $selectedCampusId
            ? $settings->whereIn('setting_key', self::COMMITTEE_KEYS)->sortBy(fn ($setting) => array_search($setting->setting_key, self::COMMITTEE_KEYS))
            : collect();
        $committeeMembers = Member::withoutGlobalScopes()
            ->whereIn('id', $committeeSettings->pluck('setting_value')->filter())
            ->get()
            ->keyBy('id');

        return view('settings.index', compact('settings', 'allCampuses', 'selectedCampusId', 'isPresident', 'cyclesByCampus', 'claimTypes', 'committeeSettings', 'committeeMembers'));
    }

    /**
     * Committee on Claims setting keys, in display order (printed on claim Forms 1 & 2).
     */
    public const COMMITTEE_KEYS = [
        'claims_committee_chair',
        'claims_committee_member_1',
        'claims_committee_member_2',
    ];

    /**
     * Search members of one campus for the Committee on Claims slots.
     */
    public function searchCommitteeMembers(Request $request)
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'string'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();
        $hasFullAccess = $user->can('view all campus data') || $user->hasAnyRole(['President', 'Treasurer']);
        abort_if(!$hasFullAccess && $user->getCachedCampusId() !== $validated['campus_id'], 403);

        $search = trim($validated['search'] ?? '');
        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $members = Member::withoutGlobalScopes()
            ->where('campus_id', $validated['campus_id'])
            ->where(function ($query) use ($search) {
                $query->where('employee_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->orderBy('last_name')
            ->limit(10)
            ->get()
            ->map(fn (Member $member) => [
                'id' => $member->id,
                'name' => Str::squish($member->full_name),
                'employee_number' => $member->employee_number,
                'position' => $member->position,
            ]);

        return response()->json($members);
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
            // Empty clears the slot; otherwise it must be a member of the setting's campus
            'claims_committee_chair', 'claims_committee_member_1', 'claims_committee_member_2' => [
                'nullable',
                'string',
                Rule::exists('members', 'id')->where('campus_id', $setting->campus_id)->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($setting) {
                    $alreadyAssigned = SystemSetting::query()
                        ->where('campus_id', $setting->campus_id)
                        ->whereIn('setting_key', self::COMMITTEE_KEYS)
                        ->where('setting_key', '!=', $setting->setting_key)
                        ->whereNull('cycle_label')
                        ->where('setting_value', $value)
                        ->exists();

                    if ($alreadyAssigned) {
                        $fail('This member is already assigned to another Committee on Claims position.');
                    }
                },
            ],
            default => ['required', 'string'],
        };

        if (!$isCycleConfiguration) {
            $request->validate([
                'setting_value' => $validationRules,
                'campus_filter' => ['nullable', 'string'],
            ], [
                'setting_value.exists' => 'The selected member does not belong to this campus.',
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
        $startMonth = $startMonth ?? null; 
        if (in_array($setting->setting_key, $cycleSettingKeys, true)) {
            
            // 1. Extract values from either the bulk request payload or the single-key payload
            if ($isCycleConfiguration) {
                $startYear = (int) $request->input('cycle_start_year');
                $startMonth = (int) $request->input('cycle_start_month');
                $duration  = (int) $request->input('cycle_years');
            } else {
                // If modifying a single key, fallback to database values for the missing parts
                $startYear = $setting->setting_key === 'claim_cycle_start_year' 
                    ? (int) $request->input('setting_value') 
                    : (int) SystemSetting::where('campus_id', $setting->campus_id)->where('setting_key', 'claim_cycle_start_year')->value('setting_value');
                $startMonth = $setting->setting_key === 'claim_cycle_start_month' 
                    ? (int) $request->input('setting_value') 
                    : (int) SystemSetting::where('campus_id', $setting->campus_id)->where('setting_key', 'claim_cycle_start_month')->value('setting_value');    
                $duration = $setting->setting_key === 'claim_cycle_years' 
                    ? (int) $request->input('setting_value') 
                    : (int) SystemSetting::where('campus_id', $setting->campus_id)->where('setting_key', 'claim_cycle_years')->value('setting_value');
            }

            if ($startYear && $duration) {
                $endYear = $startYear + $duration;
                $targetLabel = "{$startYear}-{$endYear}";

                // 2. Map structural timestamp (October 1st) matching your generation script
                // $targetStart = now()->setDate($startYear, $startMonth, 1)->startOfDay();
                $targetStart = now()->setDate($startYear, 10, 1)->startOfDay();

                $targetEnd = (clone $targetStart)->addYears($duration)->subSecond();
                // 3. See if this timestamp collides with any already initialized cycles
                $timelineOverlaps = ClaimsCycle::where('campus_id', $setting->campus_id)
                ->where(function ($query) use ($targetStart, $targetEnd) {
                    $query->where(function ($q) use ($targetStart) {
                        $q->where('start_date', '<=', $targetStart)
                          ->where('end_date', '>=', $targetStart);
                    })
                    ->orWhere(function ($q) use ($targetEnd) {
                        $q->where('start_date', '<=', $targetEnd)
                          ->where('end_date', '>=', $targetEnd);
                    })
                    ->orWhere(function ($q) use ($targetStart, $targetEnd) {
                        $q->where('start_date', '>=', $targetStart)
                          ->where('end_date', '<=', $targetEnd);
                    });
                })
                ->first();

                if ($timelineOverlaps) {
                    $errorField = $isCycleConfiguration ? 'cycle_start_year' : 'setting_value';
                    $currentCycle = ClaimsCycle::where('campus_id', $setting->campus_id)
                        ->where('start_date', '<=', $targetStart)
                        ->where('end_date', '>=', $targetStart)
                        ->first();
                    throw ValidationException::withMessages([
                        $errorField => "The selected cycle timeline '{$targetLabel}' overlaps with the existing setup for '" . ($currentCycle?->cycle_label ?? '') . "' and cannot be overridden.",
                    ]);
                }
            }
        }


        DB::transaction(function () use ($request, $setting, $isCycleConfiguration, $startMonth) {
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

                    $startYear = (int) $request->input('cycle_start_year');
                    $startMonth = (int) $request->input('cycle_start_month');
                    $durationYears = (int) $request->input('cycle_years');

                    $startDate = \Carbon\Carbon::create($startYear, $startMonth, 1)->startOfDay();
                    $endDate = $startDate->copy()->addYears($durationYears)->subDay()->endOfDay();

                    $claimsArray =[
                        'campus_id' => $setting->campus_id,
                        'cycle_label' => "{$startYear}-" . ($startYear + $durationYears),
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'duration_years' => $durationYears,
                        'start_month' => $startMonth,
                    ];

                   $claimsCycle = ClaimsCycle::firstOrCreate(
                        [
                            'campus_id' => $setting->campus_id,
                            'cycle_label' => "{$request->input('cycle_start_year')}-" . ($request->input('cycle_start_year') + $request->input('cycle_years')),
                        ],
                        [
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'duration_years' => $durationYears,
                            'start_month' => $startMonth,
                        ]
                    );

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

            // Committee assignments are not cycle-bound: no historical snapshot, empty value clears the slot
            if (in_array($setting->setting_key, self::COMMITTEE_KEYS, true)) {
                $liveSetting->update(['setting_value' => $value ?? '']);
                return;
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'The claim cycle configurations have been saved successfully.',
                'redirect' => route('settings.index', ['campus_filter' => $request->input('campus_filter')]) 
            ]);
        }
    
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
