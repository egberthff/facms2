<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
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
            ->orderBy('setting_key')
            ->orderBy('campus_id')
            ->get();

        return view('settings.index', compact('settings', 'allCampuses', 'selectedCampusId', 'isPresident'));
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
            'setting_value' => ['required', 'string'],
            'campus_filter' => ['nullable', 'string'],
        ]);
    
        $user = Auth::user();

        $hasFullAccess = $user->can('view all campus data') 
            || $user->hasAnyRole(['Federated President', 'Federated Treasurer']);
        
        abort_if(!$hasFullAccess && $user->getCachedCampusId() !== $setting->campus_id, 403);

    
        // 1. Calculate the current 2-year business cycle label dynamically
        $now = \Carbon\Carbon::now();
        $startYear = $now->month >= 10 ? $now->year : $now->year - 1;
        $endYear = $startYear + 2;
        $currentCycleLabel = "{$startYear}-{$endYear}";
    
        // 2. Automatically snapshot this historical value for the current cycle
        SystemSetting::updateOrCreate(
            [
                'campus_id'   => $setting->campus_id,
                'setting_key' => $setting->setting_key, // Dynamically keeps 'annual_limit', 'contribution_limit', etc.
                'cycle_label' => $currentCycleLabel,    // Scopes it to this cycle row
            ],
            [
                'setting_value' => $request->input('setting_value'),
                'description'   => "Historical snapshot of {$setting->setting_key} for cycle {$currentCycleLabel}",
            ]
        );
    
        // 3. Update the primary live configuration row (where cycle_label is NULL)
        $setting->update(['setting_value' => $request->input('setting_value')]);
    
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
