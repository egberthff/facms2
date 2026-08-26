<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClaimService;
use App\Services\ContributionService;

class AnnualContributionController extends Controller
{
    protected ClaimService $claimService;
    protected ContributionService $contributionService;

    public function __construct(
        ClaimService $claimService,
        ContributionService $contributionService
    ) {
        $this->claimService = $claimService;
        $this->contributionService = $contributionService;
    }

    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'contribution_year' => 'required|integer|min:2000|max:' . (now()->year + 1),
            'campus_filter'     => 'nullable|exists:campuses,id',
        ]);

        try {
            // This safely calls the method from your service layer
            $result = $this->contributionService->generateAnnualContributionsBatch((int) $validated['contribution_year'], $validated['campus_filter'] ?? null);

            return response()->json([
                'message' => "Successfully generated {$result['records_created']} records for {$result['year']} at ₱" . number_format($result['amount_applied'], 2) . " each.",
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
} // Class closes cleanly right here
