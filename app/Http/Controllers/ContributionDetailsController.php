<?php

namespace App\Http\Controllers;

use App\Models\ContributionDetails;
use App\Enums\ContributionAdjustment;
use App\Http\Requests\StoreContributionDetailsRequest;
use App\Http\Requests\UpdateContributionDetailsRequest;
use App\Services\ContributionService; 
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Exception;

class ContributionDetailsController extends Controller
{
    protected ContributionService $contributionService;
     public function __construct(
        ContributionService $contributionService,
        )
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
        $batches = $this->contributionService->getFilteredBatches($filters, $request->input('per_page', 15));

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
        $status = null;
        $contribution_type = $request->input('contribution_type', 'claims');
    
        switch ($contribution_type) {
            case 'claims':
                $contributions = $this->contributionService->getUserOwedContributions($user, $status);
                
                // Format data specifically for the Claims Table layout
                $formattedData = $contributions->map(function ($detail) {
                    $batch = $detail->contributionBatch;
                    $claim = $batch?->claim;
                    $claimant = $claim?->member;
                    $claimType = $claim?->claimType;
            
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
                        'batch_id'          => $batch?->id,
                        'amount_per_member' => (float) $batch?->amount_per_member,
                        'generated_at'      => $batch?->generated_at?->toFormattedDateString(),
                        'claim_number'      => $claim?->claim_number,
                        'claimant_name'     => $claimantName ?: 'Unknown Member',
                        'claimant_dept'     => $claimant?->department,
                        'claim_type'        => $claimType?->name ?: 'N/A',
                        'claim_description' => $claim?->description,
                    ];
                });
                break;
            
            case 'annual':
                $contributions = $this->contributionService->getUserOwedContributionsAnnual($user, $status);
                
                // Format data specifically for your newly added Annual Table layout
                $formattedData = $contributions->map(function ($annual) {
                    return [
                        'id'                        => $annual->id,
                        'reference_number'          => $annual->reference_number ?? 'N/A',
                        'contribution_year'         => $annual->contribution_year,
                        'amount_paid'               => (float) $annual->amount_paid,
                        'status'                    => $annual->status,
                        'remarks'                   => $annual->remarks,
                        'paid_at'                   => $annual->paid_at,
                        'payment_reference_number'  => $annual->payment_reference_number,
                        
                        // Eager loaded relationship arrays mapping to Blade components
                        'payment_method' => $annual->paymentMethod ? [
                            'id'   => $annual->paymentMethod->id,
                            'name' => $annual->paymentMethod->name,
                        ] : null,
                    ];
                });
                break;
            
            default:
                $formattedData = collect();
        }
            
        return view('contributions.member-contributions-monitor', [
            'contributions'     => $formattedData,
            'contribution_type' => $contribution_type
        ]);
    }


     public function pay(string $id): JsonResponse
    {
        try {
            $updatedDetail = $this->contributionService->markAsPaid($id, 'claims');
 
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

    public function contributionAdjustment(Request $request) : JsonResponse
    {
        // Validate inputs matching your form layout names and database types
        $validated = $request->validate([
            'campus_id'        => ['required', 'uuid', 'exists:campuses,id'],
            'adjustment_month' => ['required', 'date_format:Y-m'],
            'reference_month'  => ['nullable', 'date_format:Y-m'], 
            // Leverages native framework enum validation directly
            'adjustment_type'  => ['required', Rule::enum(ContributionAdjustment::class)],
            'amount'           => ['required', 'numeric'],
            'description'      => ['nullable', 'string', 'max:1000'],
        ]);
    
        try {
            $this->contributionService->contributionAdjustment($validated);
    
            return response()->json([
                'success' => true,
                'message' => 'Contribution adjustment applied successfully.'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the collection adjustment configuration.'
            ], 500);
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
