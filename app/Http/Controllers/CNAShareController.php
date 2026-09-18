<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ContributionService;

class CNAShareController extends Controller
{
    private ContributionService $contributionService;

    public function __construct(
        ContributionService $contributionService
    )
    {
        $this->contributionService = $contributionService;
    }
    public function create(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'contribution_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'campus_filter' => 'nullable|string',
        ]);


        $this->contributionService->setupCNAShare($validatedData);

        return response()->json([
            'message' => "CNA Share setup for {$validatedData['campus_filter']} has been generated successfully.",
            'year' => $validatedData['contribution_year'],
        ]);
    }
}
