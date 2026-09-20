<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ContributionService;
use App\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Exception;

class CNAShareController extends Controller
{
    private ContributionService $contributionService;
    private MemberService $memberService;

    public function __construct(
        ContributionService $contributionService,
        MemberService $memberService
    )
    {
        $this->contributionService = $contributionService;
        $this->memberService = $memberService;
    }
    public function create(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'contribution_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'contribution_amount' => 'required|numeric|min:0',
            'campus_filter' => 'nullable|string',
        ]);


        $this->contributionService->setupCNAShare($validatedData);
        $campus = $this->memberService->getCampus($validatedData['campus_filter']);

        return response()->json([
            'message' => sprintf("CNA Share setup for %s has been generated successfully.", $campus?->acronym ?? 'selected campus'),
            'year' => $validatedData['contribution_year'],
            'amount' => $validatedData['contribution_amount'], 
        ]);
    }

     public function pay(string $id): JsonResponse  
    {
        try {
            $updatedDetail = $this->contributionService->markAsPaid($id, 'cna_share');
 
            return response()->json([
                'success' => true,
                 'message' => 'CNA Share has been marked as paid successfully.',
                'data'    => $updatedDetail
            ]);
        } catch (Exception $e) {
            \Log::error(print_r($e, true));
            return response()->json([
                'success' => false,
                'message' => 'Unable to record payment line item assignment.'
            ], 404);
        }
    }
}
