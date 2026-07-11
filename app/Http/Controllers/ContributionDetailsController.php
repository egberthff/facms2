<?php

namespace App\Http\Controllers;

use App\Models\ContributionDetails;
use App\Http\Requests\StoreContributionDetailsRequest;
use App\Http\Requests\UpdateContributionDetailsRequest;
use App\Services\ContributionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class ContributionDetailsController extends Controller
{
    protected ContributionService $contributionService;
     public function __construct(ContributionService $contributionService)
    {
        $this->contributionService = $contributionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Add your custom Policy check if required
        // $this->authorize('viewAny', ContributionDetails::class);

        $filters = $request->only(['claim_id', 'start_date', 'end_date']);
        $batches = $this->contributionService->getFilteredBatches($filters, $request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $batches
        ]);
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
//  public function store(StoreContributionDetailsRequest $request): JsonResponse
//     {
//         // $this->authorize('create', ContributionBatch::class);

//         // $validated = $request->validate([
//         //     'claim_id'          => 'required|uuid|exists:claims,id',
//         //     'amount_per_member' => 'required|numeric|min:0.01',
//         // ]);

//         try {
//             $batch = $this->contributionService->createBatch([$request->claim_id, $request->amount_per_member]);
            
//             return response()->json([
//                 'success' => true,
//                 'message' => 'Contribution batch generated successfully for active members.',
//                 'data'    => $batch->load('contributionDetails')
//             ], 201);
//         } catch (Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => $e->getMessage()
//             ], 422);
//         }
//     }    

     public function myOwedContributions(Request $request)
    {
        $user = Auth::user();
        // $status = $request->input('status', 'Unpaid'); // Defaults to 'Unpaid' to see Pending contributions
        $status = null;

        $contributions = $this->contributionService->getUserOwedContributions($user, $status);

        $formattedData = $contributions->map(function ($detail) {
            $batch = $detail->contributionBatch;
            $claim = $batch?->claim;
            $claimant = $claim?->member;
            $claimType = $claim?->claimType;

            // Build full name helper cleanly
            $claimantName = trim(sprintf(
                '%s %s %s %s',
                $claimant?->first_name,
                $claimant?->middle_name,
                $claimant?->last_name,
                $claimant?->suffix
            ));

            return [
                'contribution_id'   => $detail->id,
                'amount_to_pay'     => (float) $detail->amount,
                'status'            => $detail->status,
                'paid_at'           => $detail->paid_at,

                // Batch Info
                'batch_id'          => $batch?->id,
                'amount_per_member' => (float) $batch?->amount_per_member,
                'generated_at'      => $batch?->generated_at?->toFormattedDateString(),

                // Context Info (Who and what they are contributing for)
                'claim_number'      => $claim?->claim_number,
                'claimant_name'     => $claimantName ?: 'Unknown Member',
                'claimant_dept'     => $claimant?->department,
                'claim_type'        => $claimType?->name ?: 'N/A',
                'claim_description' => $claim?->description,
            ];
        });

        $data['contributions'] = $formattedData;
        return view('contributions.member-contributions-monitor', $data);

        // return response()->json([
        //     'success' => true,
        //     'count'   => $contributions->count(),
        //     'data'    => $contributions
        // ]);
    }

     public function pay(string $id): JsonResponse
    {
        try {
            $updatedDetail = $this->contributionService->markAsPaid($id);

            return response()->json([
                'success' => true,
                'message' => 'Contribution payment recorded successfully.',
                'data'    => $updatedDetail
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to record payment line item assignment.'
            ], 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContributionDetails $contributionDetails)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContributionDetails $contributionDetails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContributionDetailsRequest $request, ContributionDetails $contributionDetails)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContributionDetails $contributionDetails)
    {
        //
    }
}
