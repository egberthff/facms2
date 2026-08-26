<?php
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ClaimService;
use Exception;

class AnnualContributionController extends Controller
{
    private ClaimService $claimService;
    public function storeBatch(Request $request, ClaimService $claimService)
    {
        $validated = $request->validate([
            'contribution_year' => 'required|integer|min:2000|max:' . (now()->year + 1),
            'campus_filter'     => ''
        ]);

        try {
            $result = $claimService->generateAnnualContributionsBatch((int) $validated['contribution_year']);

            return response()->json([
                'message' => "Successfully generated {$result['records_created']} records for {$result['year']} at ₱" . number_format($result['amount_applied'], 2) . " each.",
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
