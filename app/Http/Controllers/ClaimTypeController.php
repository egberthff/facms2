<?php

namespace App\Http\Controllers;

use App\Models\ClaimType;
use App\Http\Requests\StoreClaimTypeRequest;
use App\Http\Requests\UpdateClaimTypeRequest;

class ClaimTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreClaimTypeRequest $request)
    {
        $claimType = ClaimType::create($request->safe()->except('campus_filter'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Claim type '{$claimType->name}' has been added successfully.",
                'redirect' => route('settings.index', ['campus_filter' => $request->input('campus_filter')]),
            ]);
        }

        return redirect()
            ->route('settings.index', ['campus_filter' => $request->input('campus_filter')])
            ->with('success', "Claim type '{$claimType->name}' has been added successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(ClaimType $claimType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClaimType $claimType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClaimTypeRequest $request, ClaimType $claimType)
    {
        $claimType->update($request->safe()->except('campus_filter'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Claim type '{$claimType->name}' has been updated successfully.",
                'redirect' => route('settings.index', ['campus_filter' => $request->input('campus_filter')]),
            ]);
        }

        return redirect()
            ->route('settings.index', ['campus_filter' => $request->input('campus_filter')])
            ->with('success', "Claim type '{$claimType->name}' has been updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClaimType $claimType)
    {
        //
    }
}
