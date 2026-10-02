<?php

namespace App\Http\Controllers;

use App\Models\ContributionBatch;
use App\Models\Campus;
use App\Models\PaymentMethod;
use App\Http\Requests\StoreContributionBatchRequest;
use App\Http\Requests\UpdateContributionBatchRequest;
use Illuminate\Http\Request;
use App\Services\ContributionService;

class ContributionBatchController extends Controller
{
    protected ContributionService $contributionService;
     public function __construct(ContributionService $contributionService)
    {
        $this->contributionService = $contributionService;
    }
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
    public function store(StoreContributionBatchRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ContributionBatch $contributionBatch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContributionBatch $contributionBatch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContributionBatchRequest $request, ContributionBatch $contributionBatch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContributionBatch $contributionBatch)
    {
        //
    }

   /**
     * Display the contribution batch monitoring dashboard.
     */
    public function monitorBatch(Request $request)
    {
        $selectedCampusId = $request->input('campus_filter');
        $contributionType = strtolower(trim($request->input('contribution_type', 'claims'))); // 'claims' is the fallback default

        // 2. Conditionally fetch dashboard metric payloads based on the type chosen
        if ($contributionType === 'annual due') {
            // Calls your upcoming annual metrics logic inside the service file
            $data = $this->contributionService->getAnnualContributionData($selectedCampusId);
        } else if ($contributionType === 'cna share') {
            // Falls back to your original presidential metrics pipeline
            $data = $this->contributionService->getCNAShareContributionData($selectedCampusId);
        } else if ($contributionType === 'membership fee') {
            $data = $this->contributionService->getMembershipFeeData($selectedCampusId);
        } else {
            //Falls back to the default claims metrics
            $data = $this->contributionService->getClaimsContributionData($selectedCampusId);
        }
        $data['contribution_type'] = $contributionType;
        $data['paymentMethods'] = PaymentMethod::where('is_active', true)->get();
        return view('contributions.admin-contributions-monitor', $data);
    }

    // public function monitorBatch()
    // {
    //     // $this->authorize('viewAny', ContributionBatch::class);

    //     $data['batches'] = ContributionBatch::with(['claim.member', 'claim.claimType', 'contributionDetails.member'])
    //         ->latest('generated_at')
    //         ->get()
    //         ->map(function (ContributionBatch $batch) {
    //             $details = $batch->contributionDetails->map(function ($detail) {
    //                 $member = $detail->member;
    //                 $status = strtolower((string) $detail->status);

    //                 return [
    //                     'id'              => $detail->id,
    //                     'member_name'     => trim(sprintf('%s %s %s', $member?->first_name, $member?->middle_name, $member?->last_name)),
    //                     'employee_number' => $member?->employee_number ?? 'N/A',
    //                     'department'      => $member?->department ?? 'N/A',
    //                     'amount'          => (float) $detail->amount,
    //                     'status'          => $status,
    //                     'paid_at'         => $detail->paid_at ? \Carbon\Carbon::parse($detail->paid_at)->format('M d, Y h:i A') : '—',
    //                 ];
    //             });

    //             return [
    //                 'id'                  => $batch->id,
    //                 'claim_id'            => $batch->claim_id,
    //                 'claim_number'        => $batch->claim?->claim_number ?? 'N/A',
    //                 'claimant'            => trim(sprintf('%s %s %s', $batch->claim?->member?->first_name, $batch->claim?->member?->middle_name, $batch->claim?->member?->last_name)),
    //                 'claim_type'          => $batch->claim?->claimType?->name ?? 'N/A',
    //                 'collectible_amount'  => (float) $batch->total_amount,
    //                 'collected_amount'    => (float) $details->where('status', 'paid')->sum('amount'),
    //                 'generated_at'        => $batch->generated_at ? \Carbon\Carbon::parse($batch->generated_at)->format('M d, Y h:i A') : '—',
    //                 'total_members'       => (int) $batch->total_members,
    //                 'details'             => $details->values()->all(),
    //             ];
    //         });

    //         $data['allCampuses'] = Campus::where('is_active', true)->get();
    //         $data['isPresident'] = true;

    //     return view('contributions.admin-contributions-monitor', $data);
    // }

}
