<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\MemberService;
use App\Services\ClaimService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Service responsible for report generation.
     */
    protected ReportService $reportService;
    protected MemberService $memberService;
    protected ClaimService $claimService;

    /**
     * Constructor.
     */
    public function __construct(
        ReportService $reportService,
        MemberService $memberService,
        ClaimService $claimService
        )
    {
        $this->reportService = $reportService;
        $this->memberService = $memberService;
        $this->claimService = $claimService;
    }

    /**
     * Display the Report Center.
     *
     * Initial page load only.
     * No report is generated until the user clicks Generate Report.
     */
    public function index(): View
    {
        return view('reports.index', [

            /*
             |------------------------------------------------------------
             | Available Report Definitions
             |------------------------------------------------------------
             |
             | Configuration used by Alpine to build the UI.
             |
             */

            'reportConfiguration' => config('reports'),

            /*
             |------------------------------------------------------------
             | Campuses
             |------------------------------------------------------------
             | 
             |
             */

            'allCampuses' => $this->memberService->getCampus(),

            /*
             |------------------------------------------------------------
             | Members
             |------------------------------------------------------------
             |
             | Used by Allowance reports.
             |
             */

            'members' => $this->reportService->getMembers(),

            /*
             |------------------------------------------------------------
             | Available Allowance Cycles
             |------------------------------------------------------------
             */

            'cycles' => $this->reportService->getAllowanceCycles(),

            'claimTypes' => $this->claimService->getClaimType(),

        ]);
    }

    /**
     * Generate a report.
     *
     * Called through AJAX.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([

            'report_type' => ['required', 'string'],

            'campus_id' => ['nullable'],

            'member_id' => ['nullable'],

            'claim_type' => ['nullable', 'string'],

            'status' => ['nullable', 'string'],

            'date_from' => ['nullable', 'date'],

            'date_to' => ['nullable', 'date'],

            'reporting_year' => ['nullable', 'string'],

            'cycle' => ['nullable', 'string'],

        ]);

        $report = $this->reportService->generate(

            $validated['report_type'],

            $validated

        );

        return response()->json($report);
    }

    /**
     * Export report as PDF.
     *
     * Placeholder implementation.
     */
    // public function exportPdf(Request $request): BinaryFileResponse
    // {
    //     $validated = $request->validate([

    //         'report_type' => ['required', 'string']

    //     ]);

    //     return $this->reportService->exportPdf(

    //         $validated['report_type'],

    //         $request->all()

    //     );
    // }

    /**
     * Export report as Excel.
     *
     * Placeholder implementation.
     */
    // public function exportExcel(Request $request): BinaryFileResponse
    // {
    //     $validated = $request->validate([

    //         'report_type' => ['required', 'string']

    //     ]);

    //     return $this->reportService->exportExcel(

    //         $validated['report_type'],

    //         $request->all()

    //     );
    // }
}