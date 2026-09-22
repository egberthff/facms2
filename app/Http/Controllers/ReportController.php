<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\MemberService;
use App\Services\ClaimService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use Carbon\Carbon;
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
        $reportConfiguration = config('reports');

        // if (!Auth::user()->hasRole('Federated Treasurer')) {
        //     unset($reportConfiguration['types']['payroll-deduction-request']);
        // }

        return view('reports.index', [

            /*
             |------------------------------------------------------------
             | Available Report Definitions
             |------------------------------------------------------------
             |
             | Configuration used by Alpine to build the UI.
             |
             */

            'reportConfiguration' => $reportConfiguration,

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

            'campus_id' => ['nullable', 'string'],

            'member_id' => ['nullable', 'string'],

            'claim_type' => ['nullable', 'string'],

            'status' => ['nullable', 'string'],

            'date_from' => ['nullable', 'date'],

            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],

            'claim_month' => ['nullable', 'date_format:Y-m'],

            'payroll_month' => ['nullable', 'date_format:Y-m'],

            'reporting_year' => ['nullable', 'string'],

            'cycle' => ['nullable', 'string'],

        ]);


        if ($validated['report_type'] === 'payroll-deduction-request') {
            $validated = array_merge($validated, $request->validate([
                'claim_month' => ['required', 'date_format:Y-m'],
                'payroll_month' => ['required', 'date_format:Y-m'],
            ]));

            $validated = $this->payrollFilters($validated);
        }

        if($validated['report_type'] === 'faculty-deduction-summary') {
            $validated = array_merge($validated, $request->validate([
                'date_from' => ['required', 'date'],
                'date_to' => ['required', 'date', 'after_or_equal:date_from'],
                'campus_id' => ['nullable', 'string'],
            ]));

            $validated = $this->facultyDeductionsSummary($validated);
        }

        if($validated['report_type'] === 'medical-assistance-report') {
            $validated = array_merge($validated, $request->validate([
                'cycle' => ['required', 'string'],
                'campus_id' => ['nullable','string'],
            ]));

            $validated = $this->medicalAssistanceReport($validated);
        }


        $report = $this->reportService->generate(

            $validated['report_type'],

            $validated

        );

        return response()->json($report);
    }

    public function payrollDeductionRequest(Request $request): View
    {
        $validated = $request->validate([
            'member_id' => ['nullable', 'string'],
            'claim_type' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'claim_month' => ['required', 'date_format:Y-m'],
            'payroll_month' => ['required', 'date_format:Y-m'],
        ]);

        $filters = $this->payrollFilters($validated);
        $report = $this->reportService->generate('payroll-deduction-request', $filters);
        $treasurer = Auth::user();
        $treasurerMember = $treasurer->member()->withoutGlobalScopes()->first();
        $president = User::role('Federated President')->first(); 

        return view('reports.payroll-deduction-request-form', [
            'rows' => $report['rows'],
            'totalDeduction' => $report['total_deduction'],
            'federatedTreasurerName' => $treasurerMember?->full_name ?? $treasurer->name,
            'federatedPresidentName' => $president?->name ?? "",
            'reportPeriod' => $this->formatClaimMonth($filters),
            'payrollMonth' => $this->formatPayrollMonth($filters),
            'claimTypes' => collect($report['rows'])->pluck('claim_type')->unique()->implode(', '),
            'coverage' => collect($report['rows'])->pluck('coverage')->unique()->implode(', '),
            'generatedDate' => now()->format('F d, Y'),
        ]);
    }

    private function payrollFilters(array $filters): array
    {
        $user = Auth::user();
        // abort_unless($user->hasRole('Federated Treasurer') || $user->can('view reports'), 403);

        $filters['campus_id'] = null;
        $filters['treasurer_campus_id'] = null;

        return $filters;
    }

    private function facultyDeductionsSummary(array $filters): array
    {
        $user = Auth::user();
        abort_unless($user->hasRole('Federated Treasurer') || $user->can('view reports'), 403);

        $filters['campus_id'] = $filters['campus_id'] ?? null;
        $filters['date_from'] = $filters['date_from'] ?? null;
        $filters['date_to'] = $filters['date_to'] ?? null;
        $filters['treasurer_campus_id'] = $filters['treasurer_campus_id'] ?? null;

        return $filters;
    }

    private function medicalAssistanceReport(array $filters): array
    {
        $user = Auth::user();
        abort_unless($user->hasRole('Federated Treasurer') || $user->can('view reports'), 403);

        $filters['campus_id'] = $filters['campus_id'] ?? null;
        $filters['cycle'] = $filters['cycle'] ?? null;
        $filters['treasurer_campus_id'] = $filters['treasurer_campus_id'] ?? null;

        return $filters;
    }
 
    private function formatClaimMonth(array $filters): string
    {
        return !empty($filters['claim_month'])
            ? date('F Y', strtotime($filters['claim_month'] . '-01'))
            : 'the selected month';
    }

    private function formatPayrollMonth(array $filters): string
    {
        $date = !empty($filters['payroll_month'])
            ? $filters['payroll_month'] . '-01'
            : now()->toDateString();

        return date('F Y', strtotime($date));
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

    public function facultyDeductionsSummaryView(Request $request) {
        $data = $request->all(); 
        $reportData = $this->reportService->facultyDeductionSummary($data);

                return view('reports.faculty-deduction', $reportData);
    }

    public function medicalAssistanceReportView(Request $request) {
        $data = $request->all();
        $reportData = $this->reportService->medicalAssistanceReport($data);

        return view('reports.medical-assistance-report', $reportData);
    }


    public function monthlyClaimsSummaryReportView(Request $request) {
        $data = $request->all();
        $reportData = $this->reportService->monthlyClaimsSummary($data);

        return view('reports.unified-report-summary-template', $reportData);
    }

    public function unifiedReportView(Request $request) {
        $data = $request->all();
        $reportData = $this->reportService->unifiedReportAggregator($data);
        \Log::error(print_r($reportData, true));
        return view('reports.unified-report-summary-template', $reportData);
    }
}