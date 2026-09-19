<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ContributionService;
use App\Services\MemberService;

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
            'campus_filter' => 'nullable|string',
        ]);


        $this->contributionService->setupCNAShare($validatedData);
        $campus = $this->memberService->getCampus($validatedData['campus_filter']);

        return response()->json([
            'message' => sprintf("CNA Share setup for %s has been generated successfully.", $campus?->acronym ?? 'selected campus'),
            'year' => $validatedData['contribution_year'],
        ]);
    }
}
