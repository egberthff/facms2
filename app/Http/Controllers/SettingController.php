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

        if (! Auth::user()->can('view all campus data') && Auth::user()->getCachedCampusId() !== $setting->campus_id) {
            abort(403);
        }

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
